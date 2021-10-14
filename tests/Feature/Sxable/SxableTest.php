<?php

namespace berthott\SX\Tests\Feature\Sxable;

use berthott\SX\Facades\Sx;
use berthott\SX\Facades\Sxable;
use Illuminate\Support\Facades\Schema;

class SxableTest extends SxableTestCase
{
    public function test_sxable_found(): void
    {
        $sxables = Sxable::getSxableClasses();
        $this->assertNotEmpty($sxables);
    }
    
    public function test_entity_table_creation(): void
    {
        Sxable::getSxableClasses();
        $this->assertTrue(Schema::hasTable('entities'));
        $this->assertTrue(Schema::hasColumns('entities', [
            'survey', 'responde', 'organiza', 'stat_1', 'stat_2', 'stat_3', 'stat_4', 'stat_5', 'stat_6', 'stat_7', 'stat_8', 'stat_9', 'stat_10', 'stat_11', 'stat_12', 'stat_13', 'stat_14', 'stat_15', 'stat_16', 'stat_17', 'stat_18', 'stat_19', 'stat_20', 'created', 'modified', 'closetim', 'starttim', 'difftime', 'response', 'numberof', 'importgr', 'distribu', 'email', 'digitald', 'digit_1', 's_5', 's_2', 's_3', 'name', 's_4', 's_1', 's_6', 's_7', 's_14', 's_9', 's_15', 's_10', 's_11_1', 's_11_2', 's_11_3', 's_11_4', 's_11_5', 's_11_6', 's_11_7', 's_11_8', 's_11_9', 's_11_10', 's_11_11', 's_11_12', 's_11_13', 's_11_14', 's_11_15', 's_11_16', 's_11_17', 's_12', 's_17', 's_18', 'social_e', 'inclusio', 'number_j', 'still_ac', 'best_pra', 'validati', 'lang', 'statc_1', 'statc_2', 'statc_3', 'statc_4', 'statc_5', 'statc_6', 'statc_7', 'statc_8', 'statc_9', 'stat_21', 'statd_1', 'statd_2', 'statd_3', 'statd_4', 'statd_5', 'stats_1', 'stats_2', 'stats_3', 'stats_4', 'stats_5', 'stats_6', 'stat_22', 'stat_23', 'stat_24', 'stat_25', 'stato_1', 'stato_2', 'stato_3', 'stato_4', 'stato_5',
        ]));
        $this->assertTrue(Schema::hasTable('entity_labels'));
        $this->assertTrue(Schema::hasColumns('entity_labels', [
            'questionName', 'variableName', 'choiceValue', 'choiceText',
        ]));
        $this->assertTrue(Schema::hasTable('entity_questions'));
        $this->assertTrue(Schema::hasColumns('entity_questions', [
            'questionName', 'questionText',
        ]));
    }
    
    public function test_question_table_values(): void
    {
        Sxable::getSxableClasses();
        $questions =  [['questionName' => 'survey', 'questionText' => 'Umfrage'], ['questionName' => 'respondentid', 'questionText' => 'Teilnehmer-ID'], ['questionName' => 'organization', 'questionText' => 'Organisation'], ['questionName' => 'statinternal', 'questionText' => 'Basisstatus'], ['questionName' => 'created', 'questionText' => 'Erstellt'], ['questionName' => 'modified', 'questionText' => 'Geändert'], ['questionName' => 'closeTime', 'questionText' => 'Ende'], ['questionName' => 'startTime', 'questionText' => 'Beginn'], ['questionName' => 'diffTime', 'questionText' => 'Antwortdauer (Sekunden)'], ['questionName' => 'responseCollectSessions', 'questionText' => 'Antwortsitzungen'], ['questionName' => 'numberOfReturnedmail', 'questionText' => 'Anzahl der zurückgesendeten E-Mails'], ['questionName' => 'importgroup', 'questionText' => 'Importgruppe'], ['questionName' => 'distributionschedule', 'questionText' => 'Zeitplan für die Datenerfassung'], ['questionName' => 'email', 'questionText' => 'E-Mail'], ['questionName' => 'digitalDistributionStatus', 'questionText' => 'digitalDistributionStatus'], ['questionName' => 'digitalDistributionErrorMessage', 'questionText' => 'digitalDistributionErrorMessage'], ['questionName' => 's_5', 'questionText' => 'status'], ['questionName' => 's_2', 'questionText' => 'year'], ['questionName' => 's_3', 'questionText' => 'project'], ['questionName' => 'name', 'questionText' => 'name'], ['questionName' => 's_4', 'questionText' => 'sex'], ['questionName' => 's_1', 'questionText' => 'email'], ['questionName' => 's_6', 'questionText' => 'telephone'], ['questionName' => 's_7', 'questionText' => 'country_origin'], ['questionName' => 's_14', 'questionText' => 'name_company'], ['questionName' => 's_9', 'questionText' => 'country_foundation'], ['questionName' => 's_15', 'questionText' => 'business_idea'], ['questionName' => 's_10', 'questionText' => 'business_idea_description'], ['questionName' => 's_11', 'questionText' => 'SDG'], ['questionName' => 's_12', 'questionText' => 'focus_area'], ['questionName' => 's_17', 'questionText' => 'year_foundation'], ['questionName' => 's_18', 'questionText' => 'gender_relevance'], ['questionName' => 'social_entrepreneur', 'questionText' => 'social_entrepreneurship'], ['questionName' => 'inclusion', 'questionText' => 'inclusion'], ['questionName' => 'number_jobs', 'questionText' => 'number_jobs'], ['questionName' => 'still_active', 'questionText' => 'still_active'], ['questionName' => 'best_practice', 'questionText' => 'best_practice'], ['questionName' => 'validation', 'questionText' => 'validation'], ['questionName' => 'lang', 'questionText' => 'Sprache'], ['questionName' => 'statcompletion', 'questionText' => 'Abschlussstatus'], ['questionName' => 'statcreation', 'questionText' => 'Erstellungsstatus'], ['questionName' => 'statdistribution', 'questionText' => 'Verteilungsstatus'], ['questionName' => 'statsource', 'questionText' => 'Ursprung der Antworten'], ['questionName' => 'statcollect', 'questionText' => 'Erfassungsstatus'], ['questionName' => 'statoverall', 'questionText' => 'Gesamtstatus'], ];
        foreach ($questions as $question) {
            $this->assertDatabaseHas('entity_questions', $question);
        }
    }
}
