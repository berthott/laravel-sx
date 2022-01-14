<?php

namespace berthott\SX\Tests\Feature\Exclude;

use Illuminate\Support\Facades\Schema;

class ExcludeTest extends ExcludeTestCase
{
    public function test_entity_table_creation(): void
    {
        $this->assertTrue(Schema::hasTable('entities'));
        $this->assertTrue(Schema::hasColumns('entities', [
            'survey', 'respondentid', 'statinternal_1', 'statinternal_2', 'statinternal_3', 'statinternal_4', 'statinternal_5', 'statinternal_6', 'statinternal_7', 'statinternal_8', 'statinternal_9', 'statinternal_10', 'statinternal_11', 'statinternal_12', 'statinternal_13', 'statinternal_14', 'statinternal_15', 'statinternal_16', 'statinternal_17', 'statinternal_18', 'statinternal_19', 'statinternal_20', 'created', 'modified', 'closetime', 'starttime', 'difftime', 'responsecollectsessions', 'numberofreturnedmail', 'importgroup', 'distributionschedule', 'digitaldistributionstatus', 'digitaldistributionerrormessage', 's_5', 's_2', 's_3', 'name', 's_4', 's_1', 's_6', 's_7', 's_14', 's_9', 's_15', 's_10', 's_11_1', 's_11_2', 's_11_3', 's_11_4', 's_11_5', 's_11_6', 's_11_7', 's_11_8', 's_11_9', 's_11_10', 's_11_11', 's_11_12', 's_11_13', 's_11_14', 's_11_15', 's_11_16', 's_11_17', 's_12', 's_17', 's_18', 'social_entrepreneur', 'inclusion', 'number_jobs', 'still_active', 'best_practice', 'validation', 'lang', 'statcompletion_1', 'statcompletion_2', 'statcompletion_3', 'statcreation_1', 'statcreation_2', 'statcreation_3', 'statcreation_4', 'statcreation_5', 'statcreation_6', 'statcreation_7', 'statdistribution_1', 'statdistribution_2', 'statdistribution_3', 'statdistribution_4', 'statdistribution_5', 'statsource_1', 'statsource_2', 'statsource_3', 'statsource_4', 'statsource_5', 'statsource_6', 'statcollect_1', 'statcollect_2', 'statcollect_3', 'statcollect_4', 'statoverall_1', 'statoverall_2', 'statoverall_3', 'statoverall_4', 'statoverall_5',
        ]));
        $this->assertTrue(!Schema::hasColumns('entities', [
            'x_excluded', 'organization',
        ]));
    }

    public function test_excluded_values(): void
    {
        $entity = [
            'respondentid' => 825478429,
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
            'digitaldistributionstatus' => 1,
            'digitaldistributionerrormessage' => null,
            's_5' => 2,
            's_2' => 2021,
            's_3' => 3,
            'name' => null,
            's_4' => 2,
            's_1' => null,
            's_6' => null,
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
            's_17' => null,
            's_18' => null,
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
        ];
        $this->assertDatabaseHas('entities', $entity);
        $this->assertDatabaseMissing('entity_questions', ['variableName' => 'email']);
        $this->assertDatabaseMissing('entity_questions', ['variableName' => 'x_exclude']);
        $this->assertDatabaseMissing('entity_labels', ['variableName' => 'email']);
        $this->assertDatabaseMissing('entity_labels', ['variableName' => 'x_exclude']);
        $this->assertDatabaseMissing('entity_structure', ['variableName' => 'email']);
        $this->assertDatabaseMissing('entity_structure', ['variableName' => 'x_exclude']);
    }

    public function test_store_with_excluded(): void
    {
        $id = $this->post(route('entities.store'), [
            'form_params' => [
                    'email' => 'test@syspons.com',
                    's_2' => 3333
                ]
            ])
            ->assertStatus(200)
            ->json()['id'];
        $this->assertDatabaseHas('entities', [
            'respondentid' => $id,
            's_2' => 3333
        ]);
        $this->delete(route('entities.destroy', ['entity' => $id]))
            ->assertStatus(200);
        $this->assertDatabaseMissing('entities', ['respondentid' => $id]);
    }
}
