<?php

namespace berthott\SX\Tests\Feature\Exclude;

use Illuminate\Support\Facades\Schema;

class ExcludeTest extends ExcludeTestCase
{
    public function test_entity_table_creation(): void
    {
        $this->assertTrue(Schema::hasTable('entities'));
        $this->assertTrue(Schema::hasColumns('entities', [
            'survey', 'responde', 'stat_1', 'stat_2', 'stat_3', 'stat_4', 'stat_5', 'stat_6', 'stat_7', 'stat_8', 'stat_9', 'stat_10', 'stat_11', 'stat_12', 'stat_13', 'stat_14', 'stat_15', 'stat_16', 'stat_17', 'stat_18', 'stat_19', 'stat_20', 'created', 'modified', 'closetim', 'starttim', 'difftime', 'response', 'numberof', 'importgr', 'distribu', 'digitald', 'digit_1', 's_5', 's_2', 's_3', 'name', 's_4', 's_1', 's_6', 's_7', 's_14', 's_9', 's_15', 's_10', 's_11_1', 's_11_2', 's_11_3', 's_11_4', 's_11_5', 's_11_6', 's_11_7', 's_11_8', 's_11_9', 's_11_10', 's_11_11', 's_11_12', 's_11_13', 's_11_14', 's_11_15', 's_11_16', 's_11_17', 's_12', 's_17', 's_18', 'social_e', 'inclusio', 'number_j', 'still_ac', 'best_pra', 'validati', 'lang', 'statc_1', 'statc_2', 'statc_3', 'statc_4', 'statc_5', 'statc_6', 'statc_7', 'statc_8', 'statc_9', 'stat_21', 'statd_1', 'statd_2', 'statd_3', 'statd_4', 'statd_5', 'stats_1', 'stats_2', 'stats_3', 'stats_4', 'stats_5', 'stats_6', 'stat_22', 'stat_23', 'stat_24', 'stat_25', 'stato_1', 'stato_2', 'stato_3', 'stato_4', 'stato_5',
        ]));
        $this->assertTrue(!Schema::hasColumns('entities', [
            'x_excluded', 'organiza',
        ]));
    }

    public function test_excluded_values(): void
    {
        $entity = [
            'responde' => 825478429,
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
            'importgr' => null,
            'distribu' => null,
            'digitald' => 1,
            'digit_1' => null,
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
            'social_e' => null,
            'inclusio' => null,
            'number_j' => null,
            'still_ac' => null,
            'best_pra' => null,
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
            'responde' => $id,
            's_2' => 3333
        ]);
        $this->delete(route('entities.destroy', ['entity' => $id]))
            ->assertStatus(200);
        $this->assertDatabaseMissing('entities', ['responde' => $id]);
    }
}
