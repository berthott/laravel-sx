<?php

namespace berthott\SX\Tests\Feature\ImportRoute;

use berthott\SX\Events\RespondentsImported;
use Illuminate\Support\Facades\Event;

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
                    'statinternal_2 - Fragebogen gedruckt' => 'Nicht ausgewählt',
                    'statinternal_6 - Es wurden einige Fragen beantwortet' => 'Ausgewählt' // checks for trimming the dot
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
        $this->assertDatabaseCount('entities', 5);
    }

    public function test_import_route_fresh(): void
    {
        $this->assertDatabaseCount('entities', 4);
        $this->post(route('entities.sync'), ['fresh' => true]);
        $this->assertDatabaseCount('entities', 5);
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

    public function test_import_latest_from_database(): void
    {
        $this->assertEquals('20211018_164200', $this->testMethod(Entity::class, 'lastImport')['modifiedSince']);
    }

    public function test_import_latest_from_input_absolute(): void
    {
        $this->assertEquals('20210606_000000', $this->testMethod(Entity::class, 'lastImport', '2021-06-06 00:00:00')['modifiedSince']);
    }

    public function test_import_latest_from_input_relative(): void
    {
        $this->assertEquals('20211017_164200', $this->testMethod(Entity::class, 'lastImport', '1 day')['modifiedSince']);
    }

    public function test_respondents_imported_events(): void
    {
        Event::fake();
        $this->post(route('entities.sync'), ['fresh' => true]);
        Event::assertDispatched(RespondentsImported::class, function ($event) {
            return $event->model === Entity::class;
        });
    }
}
