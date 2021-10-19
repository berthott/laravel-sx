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
    public function createNewRespondent(array $args): Respondent
    {
        return new Respondent(SxHttpService::respondents()->create($args));
    }

    /**
     * Delete a respondent.
     */
    public function deleteRespondent(): void
    {
    }
}
