<?php

namespace berthott\SX\Services;

use berthott\SX\Facades\SxController;
use GuzzleHttp\Psr7\Response;
use League\Csv\Reader;
use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Support\Collection;

class SxService
{
    /**
     * The Survey structure.
     */
    private Collection $structure;

    /**
     * The Survey Id.
     */
    private string $survey_id;

    /**
     * Set the survey id.
     */
    public function forSurvey(string $survey_id): self
    {
        $this->survey_id = $survey_id;
        return $this;
    }

    /**
     * Initialize the Structure.
     */
    private function initStructure()
    {
        if (!isset($this->structure)) {
            $this->structure = $this->extractCsv($this->getStructureFromApi());
        }
    }

    /**
     * Get the structure from SX.
     */
    public function getStructureFromApi(): Response
    {
        return SxController::surveys()->exportStructure([
            'survey' => $this->survey_id,
            'query' => [
                'format' => 'EU',
            ],
        ]);
    }

    /**
     * Initialize the Structure.
     */
    private function extractCsv(Response $response): Collection
    {
        $collection = collect();
        $csv = Reader::createFromStream(StreamWrapper::getResource($response->getBody()));
        $csv->setHeaderOffset(0);
        $csv->setDelimiter(';');
        foreach ($csv as $record) {
            $collection->push(($record));
        }
        return $collection;
    }

    /**
     * Get the structure for the base table.
     */
    public function getBaseStructure(): Collection
    {
        $this->initStructure();
        return $this->pluckFromCollection($this->structure, 'variableName', 'subType');
    }

    /**
     * Get the questions for the questions table.
     */
    public function getQuestions(): Collection
    {
        $this->initStructure();
        return $this->pluckFromCollection($this->structure, 'questionName', 'questionText')->unique();
    }

    /**
     * Get the values for the values table.
     */
    public function getValues(): Collection
    {
        $this->initStructure();
        return $this->pluckFromCollection($this->structure->filter(function ($item) {
            return in_array($item['subType'], ['Single', 'Multiple']);
        }), 'questionName', 'variableName', 'choiceValue', 'choiceText');
    }

    /**
     * Get the values for the values table.
     */
    private function pluckFromCollection(Collection $collection, string ...$args): Collection
    {
        return $collection->map(function ($item) use ($args) {
            return array_intersect_key($item, array_fill_keys($args, ''));
        });
    }
}
