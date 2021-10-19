<?php

namespace berthott\SX\Services;

use berthott\SX\Facades\SxHttpService;
use berthott\SX\Models\Respondent;

class SxRespondentService
{
    /**
     * The Respondent Id.
     */
    private string $respondent_id;

    /**
     * The Constructor.
     */
    public function __construct(string $respondent_id = '')
    {
        $this->respondent_id = $respondent_id;
    }

    /**
     * Get a respondent.
     */
    public function getRespondent(): Respondent
    {
        return new Respondent(SxHttpService::respondents()->get([
            'respondent' => $this->respondent_id
        ]));
    }

    /**
     * Create a new respondent.
     */
    public function createNewRespondent(?array $body, ?array $query): Respondent
    {
        return new Respondent(SxHttpService::respondents()->create([
            'query' => $query,
            'body' => $body,
        ]));
    }

    /**
     * Delete a respondent.
     */
    public function deleteRespondent(): void
    {
    }
}
