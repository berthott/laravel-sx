<?php

namespace berthott\SX\Tests\Feature\Include;

use Illuminate\Support\Facades\Schema;

class IncludeTest extends IncludeTestCase
{
    public function test_entity_table_creation(): void
    {
        $this->assertTrue(Schema::hasTable('entities'));
        $this->assertTrue(Schema::hasColumns('entities', [
            'survey', 'respondentid',
        ]));
        $this->assertTrue(!Schema::hasColumns('entities', [
            'organization', 'statinternal_1', 'statinternal_2', 'statinternal_3', 'statinternal_4', 'statinternal_5', 'statinternal_6', 'statinternal_7', 'statinternal_8', 'statinternal_9', 'statinternal_10', 'statinternal_11', 'statinternal_12', 'statinternal_13', 'statinternal_14', 'statinternal_15', 'statinternal_16', 'statinternal_17', 'statinternal_18', 'statinternal_19', 'statinternal_20', 'created', 'modified', 'closetime', 'starttime', 'difftime', 'responsecollectsessions', 'numberofreturnedmail', 'importgroup', 'distributionschedule', 'email', 'digitaldistributionstatus', 'digitaldistributionerrormessage', 's_5', 's_2', 's_3', 'name', 's_4', 's_1', 's_6', 's_7', 's_14', 's_9', 's_15', 's_10', 's_11_1', 's_11_2', 's_11_3', 's_11_4', 's_11_5', 's_11_6', 's_11_7', 's_11_8', 's_11_9', 's_11_10', 's_11_11', 's_11_12', 's_11_13', 's_11_14', 's_11_15', 's_11_16', 's_11_17', 's_12', 's_17', 'gender_rel', 'social_entrepreneur', 'inclusion', 'number_jobs', 'still_active', 'best_practice', 'validation', 'lang', 'statcompletion_1', 'statcompletion_2', 'statcompletion_3', 'statcreation_1', 'statcreation_2', 'statcreation_3', 'statcreation_4', 'statcreation_5', 'statcreation_6', 'statcreation_7', 'statdistribution_1', 'statdistribution_2', 'statdistribution_3', 'statdistribution_4', 'statdistribution_5', 'statsource_1', 'statsource_2', 'statsource_3', 'statsource_4', 'statsource_5', 'statsource_6', 'statcollect_1', 'statcollect_2', 'statcollect_3', 'statcollect_4', 'statoverall_1', 'statoverall_2', 'statoverall_3', 'statoverall_4', 'statoverall_5',
        ]));
    }

    public function test_included_values(): void
    {
        $entity = [
            'survey' => 1325978,
            'respondentid' => 825478429,
        ];
        $this->assertDatabaseHas('entities', $entity);
    }
}
