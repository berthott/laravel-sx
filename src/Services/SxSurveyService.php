<?php

namespace berthott\SX\Services;

use Facades\berthott\SX\Helpers\SxHelpers;
use Facades\berthott\SX\Services\Http\SxHttpService;
use GuzzleHttp\Psr7\Response;
use League\Csv\Reader;
use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Support\Collection;
use League\Csv\Statement;
use Illuminate\Support\Str;

/**
 * Service to interact with a SX Surveys data.
 *
 * A survey has various data that is mapped in this service as follows:
 *
 * | SxSurveyService    | SX            |
 * | ---                | ---           |
 * | structure          | structure     |
 * | questions          | structure     |
 * | entities           | dataset       |
 * | labels             | labels        |
 * | /                  | questionnaire |
 * | /                  | variables     |
 *
 * Data is requested as CSV, and parsed.
 *
 * SX uses short variable names for export by default. While the SX API
 * gives us the option to export also long names, when interacting with
 * respondents, it requires short names. Therefore we export the short
 * names and use the returned structure to guess the short and full names
 * however we need them ({@see \berthott\SX\Services\SxSurveyService::guessShortVariableName()},
 * {@see \berthott\SX\Services\SxSurveyService::guessFullVariableName()}).
 */
class SxSurveyService
{
    /**
     * The sx outputs.
     */
    private Collection $structure;

    /**
     * The sx labels.
     */
    private Collection $labels;

    /**
     * The survey ID.
     */
    private string $survey_id;

    /**
     * The surveys languages.
     */
    private Collection $languages;

    /**
     * The surveys default languages.
     */
    private string $defaultLanguage;

    /**
     * The Constructor.
     */
    public function __construct(string $survey_id, array $languages, string $defaultLanguage = null)
    {
        $this->survey_id = $survey_id;
        $this->languages = collect($languages);
        $this->defaultLanguage = $defaultLanguage ?: $languages[0];
    }

    /**
     * Get the structure for the entity table.
     */
    public function getEntityStructure(): Collection
    {
        $this->initStructure();
        return $this->mapToFullVariableName(SxHelpers::pluckFromCollection($this->structure[$this->defaultLanguage], 'variableName', 'subType'));
    }

    /**
     * Get the entities from SX.
     */
    public function getEntities(array $query = []): Collection
    {
        return $this->extractCsv(SxHttpService::surveys()->exportDataset([
            'survey' => $this->survey_id,
            'query' => array_merge(
                ['format' => 'INTL_US'],
                $query,
            ),
        ]), null, true);
    }

    /**
     * Get the questions for the questions table.
     */
    public function getQuestions(string $language = null): Collection
    {
        $this->initStructure();
        $questions = $this->structure->map(fn ($structure) => $this->mapToFullVariableName(SxHelpers::pluckFromCollection($structure, 'questionName', 'variableName', 'questionText', 'subType', 'choiceValue', 'choiceText')));
        return $questions[$language ?: $this->defaultLanguage];
    }

    /**
     * Get the values for the values table.
     */
    public function getLabels(string $language = null): Collection
    {
        $this->initLabels();
        return $this->labels[$language ?: $this->defaultLanguage];
    }

    /**
     * Initialize the structure from SX.
     */
    private function initStructure()
    {
        if (!isset($this->structure)) {
            $this->structure = $this->languages->mapWithKeys(fn ($lang) => [
                $lang => $this->extractCsv(SxHttpService::surveys()->exportStructure([
                'survey' => $this->survey_id,
                'query' => [
                    'format' => 'INTL_US',
                    'lang' => $lang,
                ],
            ]))]);
        }
    }

    /**
     * Initialize the labels.
     */
    private function initLabels()
    {
        if (!isset($this->labels)) {
            $this->labels = $this->languages->mapWithKeys(fn ($lang) => [
                $lang => $this->mapToFullVariableName($this->extractCsv(SxHttpService::surveys()->exportLabels([
                    'survey' => $this->survey_id,
                    'query' => [
                        'format' => 'INTL_US',
                        'lang' => $lang,
                    ],
                ]), ['variableName', 'value', 'label'])),
            ]);
        }
    }

    /**
     * Extract a CSV response.
     */
    private function extractCsv(Response $response, array $header = null, bool $fullNameHeaders = false): Collection
    {
        $collection = collect();
        $csv = Reader::createFromStream(StreamWrapper::getResource($response->getBody()));
        $csv->setDelimiter("\t");
        $csv->addStreamFilter('convert.iconv.UTF-16/UTF-8');
        if (!$header && !$fullNameHeaders) {
            $csv->setHeaderOffset(0);
        } else {
            if ($fullNameHeaders) {
                $header = array_map(function ($shortName) {
                    return $this->guessFullVariableName($shortName);
                }, $csv->fetchOne());
                $csv->setHeaderOffset(0);
            }
            $csv = Statement::create()->process($csv, $header);
        }
        foreach ($csv as $row) {
            foreach ($row as $k => $v) {
                unset($row[$k]);
                $new_key = preg_replace('/[^a-zA-Z0-9_.]/', '', $k);
                $row[$new_key] = $v;
            }
            $row = array_map(function ($entry) {
                return !is_numeric($entry) && empty($entry) ? null : $entry;
            }, $row);
            $collection->push(($row));
        }
        return $collection;
    }

    /**
     * Guess the full SX variable name from it's short name.
     *
     * This is done by looking up the SX structure.
     */
    public function guessFullVariableName(string $shortName): string
    {
        $this->initStructure();
        $structure = $this->structure->first();
        $entry = $structure->firstWhere('variableName', $shortName);
        if (!$entry) {
            return $shortName;
        }
        $base = Str::lower($entry['questionName']);
        if ($entry['choiceValue']) {
            $questionNameEntries = $structure->where('questionName', $entry['questionName'])->values();
            $index = $questionNameEntries->search(function ($entity) use ($shortName) {
                return $entity['variableName'] === $shortName;
            });
            return $base.'_'.++$index;
        }
        return $base;
    }

    /**
     * Guess the short SX variable name from its full name.
     *
     * Guessing the short name is no simple mapping, because the short
     * names are numbered by short name and not by the long name. This
     * means if two long names begin in the same way, they'll get the
     * same short name, and the short name will be counted accordingly.
     */
    public function guessShortVariableName(string $fullName): string
    {
        $fullNameArray = explode('_', $fullName);
        if (count($fullNameArray) === 1) {
            $index = false;
            $base = $fullNameArray[0];
        } else {
            $index = intval($fullNameArray[count($fullNameArray) - 1]) > 0
                ? (int) array_pop($fullNameArray) - 1
                : false;
            $base = join('_', $fullNameArray);
        }
        $this->initStructure();
        $structure = $this->structure->first();
        $entries = $structure->filter(function ($entry) use ($base) {
            return Str::startsWith(Str::lower($entry['questionName']), Str::lower($base));
        })->sortBy('questionName', SORT_NATURAL);
        if ($entries->count() === 1) {
            return $entries->first()['variableName'];
        } elseif ($entries->count() > 1 && is_int($index)) {
            return $entries->values()[$index]['variableName'];
        } else {
            return $fullName;
        }
    }

    /**
     * Guess the full variable name for each element of a collection.
     *
     * If a variableName exists on an item of the given collection, guess
     * its full name.
     */
    private function mapToFullVariableName(Collection $collection): Collection
    {
        return $collection->map(function ($entry) {
            if (array_key_exists('variableName', $entry)) {
                $entry['variableName'] = $this->guessFullVariableName($entry['variableName']);
            }
            return $entry;
        });
    }
}
