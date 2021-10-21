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
                    'format' => 'EU',
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
        $csv->setDelimiter(';');
        $csv->addStreamFilter('convert.iconv.ISO-8859-15/UTF-8');
        if (!$header) {
            $csv->setHeaderOffset(0);
        } else {
            $csv = Statement::create()->process($csv, $header);
        }
        foreach ($csv as $record) {
            $collection->push(($record));
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
                ['format' => 'EU'],
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
                'format' => 'EU',
            ],
        ]), ['variableName', 'value', 'label']);
    }
}
