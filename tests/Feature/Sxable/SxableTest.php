<?php

namespace berthott\SX\Tests\Feature\Sxable;

use berthott\SX\Facades\Sxable;
use Illuminate\Support\Carbon;
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
        $this->assertTrue(Schema::hasTable('entities'));
        $this->assertTrue(Schema::hasColumns('entities', [
            'survey', 'responde', 'organiza', 'stat_1', 'stat_2', 'stat_3', 'stat_4', 'stat_5', 'stat_6', 'stat_7', 'stat_8', 'stat_9', 'stat_10', 'stat_11', 'stat_12', 'stat_13', 'stat_14', 'stat_15', 'stat_16', 'stat_17', 'stat_18', 'stat_19', 'stat_20', 'created', 'modified', 'closetim', 'starttim', 'difftime', 'response', 'numberof', 'importgr', 'distribu', 'email', 'digitald', 'digit_1', 's_5', 's_2', 's_3', 'name', 's_4', 's_1', 's_6', 's_7', 's_14', 's_9', 's_15', 's_10', 's_11_1', 's_11_2', 's_11_3', 's_11_4', 's_11_5', 's_11_6', 's_11_7', 's_11_8', 's_11_9', 's_11_10', 's_11_11', 's_11_12', 's_11_13', 's_11_14', 's_11_15', 's_11_16', 's_11_17', 's_12', 's_17', 's_18', 'social_e', 'inclusio', 'number_j', 'still_ac', 'best_pra', 'validati', 'lang', 'statc_1', 'statc_2', 'statc_3', 'statc_4', 'statc_5', 'statc_6', 'statc_7', 'statc_8', 'statc_9', 'stat_21', 'statd_1', 'statd_2', 'statd_3', 'statd_4', 'statd_5', 'stats_1', 'stats_2', 'stats_3', 'stats_4', 'stats_5', 'stats_6', 'stat_22', 'stat_23', 'stat_24', 'stat_25', 'stato_1', 'stato_2', 'stato_3', 'stato_4', 'stato_5',
        ]));
        $this->assertTrue(Schema::hasTable('entities_long'));
        $this->assertTrue(Schema::hasColumns('entities_long', [
            'respondent_id', 'variableName', 'value_single_multiple', 'value_string', 'value_double', 'value_datetime',
        ]));
        $this->assertTrue(Schema::hasTable('entity_labels'));
        $this->assertTrue(Schema::hasColumns('entity_labels', [
            'variableName', 'value', 'label',
        ]));
        $this->assertTrue(Schema::hasTable('entity_questions'));
        $this->assertTrue(Schema::hasColumns('entity_questions', [
            'questionName', 'questionText',
        ]));
    }
    
    public function test_question_table_values(): void
    {
        $questions = [
            ['questionName' => 'statinternal', 'variableName' => 'stat_19', 'subType' => 'Multiple', 'questionText' => 'Basisstatus', 'choiceValue' => 19, 'choiceText' => 'Abgelehnt durch zurückgesendete Mail'],
            ['questionName' => 'modified', 'variableName' => 'modified', 'subType' => 'Date', 'questionText' => 'Geändert', 'choiceValue' => '', 'choiceText' => ''],
            ['questionName' => 's_10', 'variableName' => 's_10', 'subType' => 'String', 'questionText' => 'business_idea_description', 'choiceValue' => '', 'choiceText' => ''],
            ['questionName' => 'number_jobs', 'variableName' => 'number_j', 'subType' => 'Double', 'questionText' => 'number_jobs', 'choiceValue' => '', 'choiceText' => ''],
            ['questionName' => 'lang', 'variableName' => 'lang', 'subType' => 'Single', 'questionText' => 'Sprache', 'choiceValue' => '', 'choiceText' => ''],
        ];
        foreach ($questions as $question) {
            $this->assertDatabaseHas('entity_questions', $question);
        }
    }

    public function test_label_table_values(): void
    {
        $labels = [
            ['variableName' => 'survey', 'value' => 1325978, 'label' => 'HF 4 - GfE Applicants/participants'],
            ['variableName' => 'digitald', 'value' => 1, 'label' => '1'],
            ['variableName' => 's_5', 'value' => 3, 'label' => 'teilnehmend ohne Gründung'],
            ['variableName' => 's_11_1', 'value' => 1, 'label' => 'Ausgewählt'],
            ['variableName' => 's_12', 'value' => 2, 'label' => 'Demokratie, Zivilgesellschaft und öffentliche Verwaltung'],
        ];
        foreach ($labels as $label) {
            $this->assertDatabaseHas('entity_labels', $label);
        }
    }

    public function test_entity_table_values(): void
    {
        Carbon::setTestNow();
        $entity = [
            'survey' => 1325978,
            'responde' => 825478429,
            'organiza' => 269318,
            'stat_1' => 1,
            'stat_2' => 0,
            'stat_3' => 0,
            'stat_4' => 0,
            'stat_5' => 0,
            'stat_6' => 1,
            'stat_7' => 1,
            'stat_8' => 1,
            'stat_9' => 0,
            'stat_10' => 0,
            'stat_11' => 0,
            'stat_12' => 0,
            'stat_13' => 1,
            'stat_14' => 1,
            'stat_15' => 0,
            'stat_16' => 0,
            'stat_17' => 0,
            'stat_18' => 0,
            'stat_19' => 0,
            'stat_20' => 0,
            'created' => '2021-09-02 18:49:08',
            'modified' => '2021-10-18 16:42:00',
            'closetim' => '2021-09-02 18:52:40',
            'starttim' => '2021-09-02 18:50:24',
            'difftime' => 135.407,
            'response' => 2,
            'numberof' => 0,
            'importgr' => '',
            'distribu' => '',
            'email' => 'henrike.junge@syspons.com',
            'digitald' => 1,
            'digit_1' => '',
            's_5' => 2,
            's_2' => 2021,
            's_3' => 3,
            'name' => '',
            's_4' => 2,
            's_1' => '',
            's_6' => '',
            's_7' => 1,
            's_14' => 'Female Future',
            's_9' => 1,
            's_15' => 'Frauenhaus in Georgien im ländlichen Gebiet',
            's_10' => 'Frauenhaus in Georgien im ländlichen Gebiet',
            's_11_1' => 0,
            's_11_2' => 0,
            's_11_3' => 1,
            's_11_4' => 0,
            's_11_5' => 1,
            's_11_6' => 0,
            's_11_7' => 0,
            's_11_8' => 0,
            's_11_9' => 0,
            's_11_10' => 0,
            's_11_11' => 0,
            's_11_12' => 0,
            's_11_13' => 0,
            's_11_14' => 0,
            's_11_15' => 0,
            's_11_16' => 0,
            's_11_17' => 0,
            's_12' => 2,
            's_17' => '',
            's_18' => '',
            'social_e' => '',
            'inclusio' => '',
            'number_j' => '',
            'still_ac' => '',
            'best_pra' => '',
            'validati' => 1,
            'lang' => 2,
            'statc_1' => 1,
            'statc_2' => 1,
            'statc_3' => 0,
            'statc_4' => 1,
            'statc_5' => 0,
            'statc_6' => 0,
            'statc_7' => 0,
            'statc_8' => 0,
            'statc_9' => 0,
            'stat_21' => 0,
            'statd_1' => 0,
            'statd_2' => 0,
            'statd_3' => 1,
            'statd_4' => 0,
            'statd_5' => 0,
            'stats_1' => 1,
            'stats_2' => 1,
            'stats_3' => 0,
            'stats_4' => 0,
            'stats_5' => 0,
            'stats_6' => 0,
            'stat_22' => 0,
            'stat_23' => 0,
            'stat_24' => 1,
            'stat_25' => 0,
            'stato_1' => 0,
            'stato_2' => 0,
            'stato_3' => 0,
            'stato_4' => 1,
            'stato_5' => 0,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ];
        $this->assertDatabaseHas('entities', $entity);
    }
    
    public function test_long_table_values(): void
    {
        $this->assertDatabaseCount('entities_long', 412); // 4 entries á 103
        // value_single_multiple
        $this->assertDatabaseHas('entities_long', [
            'respondent_id' => 825478429,
            'variableName' => 'survey',
            'value_single_multiple' => 1325978,
            'value_string' => null,
            'value_double' => null,
            'value_datetime' => null,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);
        // value_double
        $this->assertDatabaseHas('entities_long', [
            'respondent_id' => 825478429,
            'variableName' => 'response',
            'value_single_multiple' => null,
            'value_string' => null,
            'value_double' => 2,
            'value_datetime' => null,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);
        // value_string
        $this->assertDatabaseHas('entities_long', [
            'respondent_id' => 825478429,
            'variableName' => 's_15',
            'value_single_multiple' => null,
            'value_string' => 'Frauenhaus in Georgien im ländlichen Gebiet',
            'value_double' => null,
            'value_datetime' => null,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);
        // value_datetime
        $this->assertDatabaseHas('entities_long', [
            'respondent_id' => 825478429,
            'variableName' => 'created',
            'value_single_multiple' => null,
            'value_string' => null,
            'value_double' => null,
            'value_datetime' => '2021-09-02 18:49:08',
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);
    }
}
