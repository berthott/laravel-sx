<?php

namespace berthott\SX\Services;

use berthott\SX\Facades\Helpers;
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
     * The Survey Id.
     */
    private string $survey_id;

    /**
     * The Constructor.
     */
    public function __construct(string $survey_id)
    {
        $this->survey_id = $survey_id;
    }

    /**
     * Initialize the structure.
     */
    private function initStructure()
    {
        if (!isset($this->structure)) {
            $this->structure = $this->extractCsv(SxHttpService::surveys()->exportStructure([
                'survey' => $this->survey_id,
                'query' => [
                    'format' => 'INTL_US',
                ],
            ]));
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
    public function getEntityStructure(): Collection
    {
        $this->initStructure();
        return $this->mapToFullVariableName(Helpers::pluckFromCollection($this->structure, 'variableName', 'subType'));
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
    public function getQuestions(): Collection
    {
        $this->initStructure();
        return $this->mapToFullVariableName(Helpers::pluckFromCollection($this->structure, 'questionName', 'variableName', 'questionText', 'subType', 'choiceValue', 'choiceText'));
    }

    /**
     * Get the values for the values table.
     */
    public function getLabels(): Collection
    {
        return $this->mapToFullVariableName($this->extractCsv(SxHttpService::surveys()->exportLabels([
            'survey' => $this->survey_id,
            'query' => [
                'format' => 'INTL_US',
            ],
        ]), ['variableName', 'value', 'label']));
    }

    /**
     * Return a last import as query array.
     */
    public function guessFullVariableName(string $shortName): string
    {
        $this->initStructure();
        $entry = $this->structure->firstWhere('variableName', $shortName);
        if (!$entry) {
            return $shortName;
        }
        $base = Str::lower($entry['questionName']);
        if ($entry['choiceValue']) {
            $questionNameEntries = $this->structure->where('questionName', $entry['questionName'])->values();
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
        $entries = $this->structure->filter(function ($entry) use ($base) {
            return Str::lower($entry['questionName']) === Str::lower($base);
        });
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
