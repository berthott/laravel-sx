<?php

namespace berthott\SX\Services;

use berthott\SX\Facades\SxHelpers;
use berthott\SX\Facades\SxHttpService;
use GuzzleHttp\Psr7\Response;
use League\Csv\Reader;
use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Support\Collection;
use League\Csv\Statement;
use Illuminate\Support\Str;

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
     * The Survey Id.
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
     * Initialize the structure.
     */
    private function initStructure()
    {
        if (!isset($this->structure)) {
            $this->structure = $this->languages->mapWithKeys(fn($lang) => [
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
     * Initialize the Structure.
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
            $row = array_map(function ($entry) {
                return !is_numeric($entry) && empty($entry) ? null : $entry;
            }, $row);
            $collection->push(($row));
        }
        return $collection;
    }

    /**
     * Get the structure for the entity table.
     */
    public function getEntityStructure(string $language = null): Collection
    {
        $this->initStructure();
        $entityStructure = $this->structure->map(fn($structure) => $this->mapToFullVariableName(SxHelpers::pluckFromCollection($structure, 'variableName', 'subType')));
        return $entityStructure[$language ?: $this->defaultLanguage];
    }

    /**
     * Get the entities.
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
        $questions = $this->structure->map(fn($structure) => $this->mapToFullVariableName(SxHelpers::pluckFromCollection($structure, 'questionName', 'variableName', 'questionText', 'subType', 'choiceValue', 'choiceText')));
        return $questions[$language ?: $this->defaultLanguage];
    }

    /**
     * Initialize the labels.
     */
    private function initLabels()
    {
        if (!isset($this->labels)) {
            $this->labels = $this->languages->mapWithKeys(fn($lang) => [
                $lang => $this->mapToFullVariableName($this->extractCsv(SxHttpService::surveys()->exportLabels([
                    'survey' => $this->survey_id,
                    'query' => [
                        'format' => 'INTL_US',
                    ],
                ]), ['variableName', 'value', 'label'])),
            ]);
        }
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
     * Return a last import as query array.
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
     * Return a last import as query array.
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

    private function mapToFullVariableName(Collection $collection): Collection
    {
        return $collection->map(function ($entry) {
            $entry['variableName'] = $this->guessFullVariableName($entry['variableName']);
            return $entry;
        });
    }
}
