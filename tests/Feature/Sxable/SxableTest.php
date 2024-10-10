<?php

namespace berthott\SX\Tests\Feature\Sxable;

use Facades\berthott\SX\Services\SxableService;
use berthott\SX\Services\SxSurveyService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class SxableTest extends SxableTestCase
{
    public function test_sxable_found(): void
    {
        $sxables = SxableService::getTargetableClasses();
        $this->assertNotEmpty($sxables);
    }

    public function test_entity_table_creation(): void
    {
        $this->assertTrue(Schema::hasTable('entities'));
        $this->assertTrue(Schema::hasColumns('entities', [
            'survey', 'respondentid', 'organization', 'statinternal_1', 'statinternal_2', 'statinternal_3', 'statinternal_4', 'statinternal_5', 'statinternal_6', 'statinternal_7', 'statinternal_8', 'statinternal_9', 'statinternal_10', 'statinternal_11', 'statinternal_12', 'statinternal_13', 'statinternal_14', 'statinternal_15', 'statinternal_16', 'statinternal_17', 'statinternal_18', 'statinternal_19', 'statinternal_20', 'created', 'modified', 'closetime', 'starttime', 'difftime', 'responsecollectsessions', 'numberofreturnedmail', 'importgroup', 'distributionschedule', 'email', 'digitaldistributionstatus', 'digitaldistributionerrormessage', 's_5', 's_2', 's_3', 'name', 's_4', 's_1', 's_6', 's_7', 'generated_id', 's_9', 's_15', 's_10', 's_11_1', 's_11_2', 's_11_3', 's_11_4', 's_11_5', 's_11_6', 's_11_7', 's_11_8', 's_11_9', 's_11_10', 's_11_11', 's_11_12', 's_11_13', 's_11_14', 's_11_15', 's_11_16', 's_11_17', 's_12', 's_17', 'gender_rel', 'social_entrepreneur', 'inclusion', 'number_jobs', 'still_active', 'best_practice', 'validation', 'lang', 'statcompletion_1', 'statcompletion_2', 'statcompletion_3', 'statcreation_1', 'statcreation_2', 'statcreation_3', 'statcreation_4', 'statcreation_5', 'statcreation_6', 'statcreation_7', 'statdistribution_1', 'statdistribution_2', 'statdistribution_3', 'statdistribution_4', 'statdistribution_5', 'statsource_1', 'statsource_2', 'statsource_3', 'statsource_4', 'statsource_5', 'statsource_6', 'statcollect_1', 'statcollect_2', 'statcollect_3', 'statcollect_4', 'statoverall_1', 'statoverall_2', 'statoverall_3', 'statoverall_4', 'statoverall_5',
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
        $this->assertTrue(Schema::hasTable('entity_structure'));
        $this->assertTrue(Schema::hasColumns('entity_structure', [
            'variableName', 'subType',
        ]));
    }

    public function test_structure_table_values(): void
    {
        $structure = [
            'survey' => 'Single',
            'respondentid' => 'Double',
            'organization' => 'Single',
            'statinternal_1' => 'Multiple',
            'statinternal_2' => 'Multiple',
            'statinternal_3' => 'Multiple',
            'statinternal_4' => 'Multiple',
            'statinternal_5' => 'Multiple',
            'statinternal_6' => 'Multiple',
            'statinternal_7' => 'Multiple',
            'statinternal_8' => 'Multiple',
            'statinternal_9' => 'Multiple',
            'statinternal_10' => 'Multiple',
            'statinternal_11' => 'Multiple',
            'statinternal_12' => 'Multiple',
            'statinternal_13' => 'Multiple',
            'statinternal_14' => 'Multiple',
            'statinternal_15' => 'Multiple',
            'statinternal_16' => 'Multiple',
            'statinternal_17' => 'Multiple',
            'statinternal_18' => 'Multiple',
            'statinternal_19' => 'Multiple',
            'statinternal_20' => 'Multiple',
            'created' => 'Date',
            'modified' => 'Date',
            'closetime' => 'Date',
            'starttime' => 'Date',
            'difftime' => 'Double',
            'responsecollectsessions' => 'Double',
            'numberofreturnedmail' => 'Double',
            'importgroup' => 'Double',
            'distributionschedule' => 'Single',
            'email' => 'String',
            'digitaldistributionstatus' => 'Single',
            'digitaldistributionerrormessage' => 'String',
            's_5' => 'Single',
            's_2' => 'Double',
            's_3' => 'Single',
            'name' => 'String',
            's_4' => 'Single',
            's_1' => 'String',
            's_6' => 'String',
            's_7' => 'Single',
            'generated_id' => 'String',
            'unique_id' => 'String',
            's_9' => 'Single',
            's_15' => 'String',
            's_10' => 'String',
            's_11_1' => 'Multiple',
            's_11_2' => 'Multiple',
            's_11_3' => 'Multiple',
            's_11_4' => 'Multiple',
            's_11_5' => 'Multiple',
            's_11_6' => 'Multiple',
            's_11_7' => 'Multiple',
            's_11_8' => 'Multiple',
            's_11_9' => 'Multiple',
            's_11_10' => 'Multiple',
            's_11_11' => 'Multiple',
            's_11_12' => 'Multiple',
            's_11_13' => 'Multiple',
            's_11_14' => 'Multiple',
            's_11_15' => 'Multiple',
            's_11_16' => 'Multiple',
            's_11_17' => 'Multiple',
            's_12' => 'Single',
            's_17' => 'Double',
            'gender_rel' => 'Single',
            'social_entrepreneur' => 'Single',
            'inclusion' => 'Single',
            'number_jobs' => 'Double',
            'still_active' => 'Single',
            'best_practice' => 'Single',
            'validation' => 'Single',
            'lang' => 'Single',
            'statcompletion_1' => 'Multiple',
            'statcompletion_2' => 'Multiple',
            'statcompletion_3' => 'Multiple',
            'statcreation_1' => 'Multiple',
            'statcreation_2' => 'Multiple',
            'statcreation_3' => 'Multiple',
            'statcreation_4' => 'Multiple',
            'statcreation_5' => 'Multiple',
            'statcreation_6' => 'Multiple',
            'statcreation_7' => 'Multiple',
            'statdistribution_1' => 'Multiple',
            'statdistribution_2' => 'Multiple',
            'statdistribution_3' => 'Multiple',
            'statdistribution_4' => 'Multiple',
            'statdistribution_5' => 'Multiple',
            'statsource_1' => 'Multiple',
            'statsource_2' => 'Multiple',
            'statsource_3' => 'Multiple',
            'statsource_4' => 'Multiple',
            'statsource_5' => 'Multiple',
            'statsource_6' => 'Multiple',
            'statcollect_1' => 'Multiple',
            'statcollect_2' => 'Multiple',
            'statcollect_3' => 'Multiple',
            'statcollect_4' => 'Multiple',
            'statoverall_1' => 'Multiple',
            'statoverall_2' => 'Multiple',
            'statoverall_3' => 'Multiple',
            'statoverall_4' => 'Multiple',
            'statoverall_5' => 'Multiple',
            ];
        foreach ($structure as $key => $value) {
            $this->assertDatabaseHas('entity_structure', [
                'variableName' => $key,
                'subType' => $value
            ]);
        }
    }

    public function test_question_table_values(): void
    {
        $questions = [
            ['questionName' => 'statinternal', 'variableName' => 'statinternal_19', 'subType' => 'Multiple', 'questionText' => 'Basisstatus', 'choiceValue' => 19, 'choiceText' => 'Abgelehnt durch zurückgesendete Mail'],
            ['questionName' => 'modified', 'variableName' => 'modified', 'subType' => 'Date', 'questionText' => 'Geändert', 'choiceValue' => null, 'choiceText' => null],
            ['questionName' => 's_10', 'variableName' => 's_10', 'subType' => 'String', 'questionText' => 'business_idea_description', 'choiceValue' => null, 'choiceText' => null],
            ['questionName' => 'number_jobs', 'variableName' => 'number_jobs', 'subType' => 'Double', 'questionText' => 'number_jobs', 'choiceValue' => null, 'choiceText' => null],
            ['questionName' => 'lang', 'variableName' => 'lang', 'subType' => 'Single', 'questionText' => 'Sprache', 'choiceValue' => null, 'choiceText' => null],
        ];
        foreach ($questions as $question) {
            $this->assertDatabaseHas('entity_questions', $question);
        }
    }

    public function test_label_table_values(): void
    {
        $labels = [
            ['variableName' => 'survey', 'value' => 1325978, 'label' => 'HF 4 - GfE Applicants/participants'],
            ['variableName' => 'digitaldistributionstatus', 'value' => 1, 'label' => '1'],
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
            'respondentid' => 825478429,
            'organization' => 269318,
            'statinternal_1' => 1,
            'statinternal_2' => 0,
            'statinternal_3' => 0,
            'statinternal_4' => 0,
            'statinternal_5' => 0,
            'statinternal_6' => 1,
            'statinternal_7' => 1,
            'statinternal_8' => 1,
            'statinternal_9' => 0,
            'statinternal_10' => 0,
            'statinternal_11' => 0,
            'statinternal_12' => 0,
            'statinternal_13' => 1,
            'statinternal_14' => 1,
            'statinternal_15' => 0,
            'statinternal_16' => 0,
            'statinternal_17' => 0,
            'statinternal_18' => 0,
            'statinternal_19' => 0,
            'statinternal_20' => 0,
            'created' => '2021-09-02 18:49:08',
            'modified' => '2021-10-18 16:42:00',
            'closetime' => '2021-09-02 18:52:40',
            'starttime' => '2021-09-02 18:50:24',
            'difftime' => 135.407,
            'responsecollectsessions' => 2,
            'numberofreturnedmail' => 0,
            'importgroup' => null,
            'distributionschedule' => null,
            'email' => 'henrike.junge@syspons.com',
            'digitaldistributionstatus' => 1,
            'digitaldistributionerrormessage' => null,
            's_5' => 2,
            's_2' => 2021,
            's_3' => 3,
            'name' => null,
            's_4' => 0,
            's_1' => null,
            's_6' => null,
            's_7' => 1,
            'generated_id' => 'GEN001',
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
            's_17' => null,
            'gender_rel' => null,
            'social_entrepreneur' => null,
            'inclusion' => null,
            'number_jobs' => null,
            'still_active' => null,
            'best_practice' => null,
            'validation' => 1,
            'lang' => 2,
            'statcompletion_1' => 1,
            'statcompletion_2' => 1,
            'statcompletion_3' => 0,
            'statcreation_1' => 1,
            'statcreation_2' => 0,
            'statcreation_3' => 0,
            'statcreation_4' => 0,
            'statcreation_5' => 0,
            'statcreation_6' => 0,
            'statcreation_7' => 0,
            'statdistribution_1' => 0,
            'statdistribution_2' => 0,
            'statdistribution_3' => 1,
            'statdistribution_4' => 0,
            'statdistribution_5' => 0,
            'statsource_1' => 1,
            'statsource_2' => 1,
            'statsource_3' => 0,
            'statsource_4' => 0,
            'statsource_5' => 0,
            'statsource_6' => 0,
            'statcollect_1' => 0,
            'statcollect_2' => 0,
            'statcollect_3' => 1,
            'statcollect_4' => 0,
            'statoverall_1' => 0,
            'statoverall_2' => 0,
            'statoverall_3' => 0,
            'statoverall_4' => 1,
            'statoverall_5' => 0,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ];
        $this->assertDatabaseHas('entities', $entity);
    }

    public function test_long_table_values(): void
    {
        $this->assertDatabaseCount('entities_long', 432); // 4 entries á 108
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
            'variableName' => 'responsecollectsessions',
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

    public function test_guess_full_variable_name(): void
    {
        $service = new SxSurveyService('mocked', ['de']);
        $this->assertEquals('responsecollectsessions', $service->guessFullVariableName('response'));
        $this->assertEquals('statcreation_2', $service->guessFullVariableName('statc_5'));
        $this->assertEquals('statdistribution_1', $service->guessFullVariableName('statd_1'));
        $this->assertEquals('s_11_17', $service->guessFullVariableName('s_11_17'));
        $this->assertEquals('gender_rel', $service->guessFullVariableName('gender_r'));
    }

    public function test_guess_short_variable_name(): void
    {
        $service = new SxSurveyService('mocked', ['de']);
        $this->assertEquals('response', $service->guessShortVariableName('responsecollectsessions'));
        $this->assertEquals('statc_5', $service->guessShortVariableName('statcreation_2'));
        $this->assertEquals('statd_1', $service->guessShortVariableName('statdistribution_1'));
        $this->assertEquals('gender_r', $service->guessShortVariableName('gender_rel'));
    }
}
