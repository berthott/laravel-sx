<?php

namespace berthott\SX\Services;

use Facades\berthott\SX\Services\Http\SxHttpService;
use berthott\SX\Models\Respondent;

/*
 * Service to manage SX Respondents.
 */
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
     * Get an existing respondent.
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
     * Update a respondent.
     */
    public function updateRespondentAnswers(array $args): Respondent
    {
        return new Respondent(SxHttpService::respondents()->updateAnswers(array_merge(
            ['respondent' => $this->respondent_id],
            $args,
        )));
    }

    /**
     * Delete a respondent.
     */
    public function deleteRespondent(): string
    {
        return SxHttpService::respondents()->delete([
            'respondent' => $this->respondent_id
        ])->getBody()->getContents();
    }
}
