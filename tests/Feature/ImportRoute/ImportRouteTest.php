<?php

namespace berthott\SX\Tests\Feature\ImportRoute;

class ImportRouteTest extends ImportRouteTestCase
{
    public function test_import_route(): void
    {
        $id = [
            'respondentid' => 841931211,
            'statinternal_2' => 0
        ];
        $this->assertDatabaseCount('entities', 4);
        $this->assertDatabaseMissing('entities', $id);
        $this->post(route('entities.sync'))
            ->assertStatus(200)
            ->assertJson([$id]);
        $this->assertDatabaseCount('entities', 5);
        $this->assertDatabaseHas('entities', $id);
    }
    
    public function test_import_route_labeled(): void
    {
        $this->post(route('entities.sync', [ 'labeled' => true ]))
            ->assertStatus(200)
            ->assertJson([
                [
                    'respondentid' => 841931211,
                    'statinternal_2 - Fragebogen gedruckt' => 'Nicht ausgewählt'
                ]
            ]);
        $this->post(route('entities.sync', [
            'labeled' => true,
            'force' => true
        ]))
            ->assertStatus(200)
            ->assertJson([
                [
                    'respondentid' => 841931211,
                    'statinternal_2 - Fragebogen gedruckt' => 'Nicht ausgewählt'
                ]
            ]);
    }

    public function test_import_route_validation(): void
    {
        $this->assertDatabaseCount('entities', 4);
        $this->post(route('entities.sync'), ['fresh' => 'yes'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('fresh');
        $this->assertDatabaseCount('entities', 4);
        $this->post(route('entities.sync'), ['fresh' => true]);
        $this->assertDatabaseCount('entities', 1);
    }

    public function test_import_route_fresh(): void
    {
        $this->assertDatabaseCount('entities', 4);
        $this->post(route('entities.sync'), ['fresh' => true]);
        $this->assertDatabaseCount('entities', 1);
    }

    public function test_import_route_update(): void
    {
        $this->assertDatabaseCount('entities', 4);
        $this->post(route('entities.sync'));
        $this->assertDatabaseCount('entities', 5);
        $this->assertDatabaseHas('entities', [
            'respondentid' => 841931211,
            's_2' => 2020,
        ]);
        $this->assertDatabaseHas('entities_long', [
            'respondent_id' => 841931211,
            'variableName' => 's_2',
            'value_double' => 2020,
        ]);
        $this->post(route('entities.sync'));
        $this->assertDatabaseCount('entities', 5);
        $this->assertDatabaseMissing('entities', [
            'respondentid' => 841931211,
            's_2' => 2020,
        ]);
        $this->assertDatabaseMissing('entities_long', [
            'respondent_id' => 841931211,
            'variableName' => 's_2',
            'value_double' => 2020,
        ]);
        $this->assertDatabaseHas('entities', [
            'respondentid' => 841931211,
            's_2' => 2021,
        ]);
        $this->assertDatabaseHas('entities_long', [
            'respondent_id' => 841931211,
            'variableName' => 's_2',
            'value_double' => 2021,
        ]);
    }
}
