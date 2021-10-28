<?php

namespace berthott\SX\Services;

use berthott\SX\Facades\Helpers;
use berthott\SX\Facades\SxHttpService;
use GuzzleHttp\Psr7\Response;
use League\Csv\Reader;
use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Support\Collection;
use League\Csv\Statement;

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
    private function extractCsv(Response $response, array $header = null): Collection
    {
        $collection = collect();
        $csv = Reader::createFromStream(StreamWrapper::getResource($response->getBody()));
        $csv->setDelimiter("\t");
        $csv->addStreamFilter('convert.iconv.UTF-16/UTF-8');
        if (!$header) {
            $csv->setHeaderOffset(0);
        } else {
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
        return Helpers::pluckFromCollection($this->structure, 'variableName', 'subType');
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
        ]));
    }

    /**
     * Get the questions for the questions table.
     */
    public function getQuestions(): Collection
    {
        $this->initStructure();
        return Helpers::pluckFromCollection($this->structure, 'questionName', 'variableName', 'questionText', 'subType', 'choiceValue', 'choiceText');
    }

    /**
     * Get the values for the values table.
     */
    public function getLabels(): Collection
    {
        return $this->extractCsv(SxHttpService::surveys()->exportLabels([
            'survey' => $this->survey_id,
            'query' => [
                'format' => 'INTL_US',
            ],
        ]), ['variableName', 'value', 'label']);
    }
}
