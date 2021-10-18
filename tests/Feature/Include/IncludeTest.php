<?php

namespace berthott\SX\Tests\Feature\Include;

use Illuminate\Support\Facades\Schema;

class IncludeTest extends IncludeTestCase
{
    public function test_entity_table_creation(): void
    {
        $this->assertTrue(Schema::hasTable('entities'));
        $this->assertTrue(Schema::hasColumns('entities', [
            'survey', 'responde',
        ]));
        $this->assertTrue(!Schema::hasColumns('entities', [
            'organiza', 'stat_1', 'stat_2', 'stat_3', 'stat_4', 'stat_5', 'stat_6', 'stat_7', 'stat_8', 'stat_9', 'stat_10', 'stat_11', 'stat_12', 'stat_13', 'stat_14', 'stat_15', 'stat_16', 'stat_17', 'stat_18', 'stat_19', 'stat_20', 'created', 'modified', 'closetim', 'starttim', 'difftime', 'response', 'numberof', 'importgr', 'distribu', 'email', 'digitald', 'digit_1', 's_5', 's_2', 's_3', 'name', 's_4', 's_1', 's_6', 's_7', 's_14', 's_9', 's_15', 's_10', 's_11_1', 's_11_2', 's_11_3', 's_11_4', 's_11_5', 's_11_6', 's_11_7', 's_11_8', 's_11_9', 's_11_10', 's_11_11', 's_11_12', 's_11_13', 's_11_14', 's_11_15', 's_11_16', 's_11_17', 's_12', 's_17', 's_18', 'social_e', 'inclusio', 'number_j', 'still_ac', 'best_pra', 'validati', 'lang', 'statc_1', 'statc_2', 'statc_3', 'statc_4', 'statc_5', 'statc_6', 'statc_7', 'statc_8', 'statc_9', 'stat_21', 'statd_1', 'statd_2', 'statd_3', 'statd_4', 'statd_5', 'stats_1', 'stats_2', 'stats_3', 'stats_4', 'stats_5', 'stats_6', 'stat_22', 'stat_23', 'stat_24', 'stat_25', 'stato_1', 'stato_2', 'stato_3', 'stato_4', 'stato_5',
        ]));
    }

    public function test_included_values(): void
    {
        $entity = [
            'survey' => 1325978,
            'responde' => 825478429,
        ];
        $this->assertDatabaseHas('entities', $entity);
    }
}
