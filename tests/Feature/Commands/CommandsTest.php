<?php

namespace berthott\SX\Tests\Feature\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class CommandsTest extends CommandsTestCase
{
    public function test_init_command(): void
    {
        //Artisan::call('sx:init'); gets called in setUp()
        $this->assertTrue(Schema::hasTable('entities'));
        $this->assertTrue(Schema::hasTable('entities_long'));
        $this->assertTrue(Schema::hasTable('entity_labels'));
        $this->assertTrue(Schema::hasTable('entity_questions'));
        $this->assertTrue(Schema::hasTable('entity_structure'));
    }

    public function test_drop_command(): void
    {
        Artisan::call('sx:drop');
        $this->assertFalse(Schema::hasTable('entities'));
        $this->assertFalse(Schema::hasTable('entities_long'));
        $this->assertFalse(Schema::hasTable('entity_labels'));
        $this->assertFalse(Schema::hasTable('entity_questions'));
        $this->assertFalse(Schema::hasTable('entity_structure'));
    }

    public function test_import_command(): void
    {
        $id = ['responde' => 841931211];
        $this->assertDatabaseCount('entities', 4);
        $this->assertDatabaseCount('dummies', 4);
        $this->assertDatabaseMissing('entities', $id);
        Artisan::call('sx:import');
        $this->assertDatabaseCount('entities', 5);
        $this->assertDatabaseCount('dummies', 5);
        $this->assertDatabaseHas('entities', $id);
    }

    public function test_import_command_only_entities(): void
    {
        $id = ['responde' => 841931211];
        $this->assertDatabaseCount('entities', 4);
        $this->assertDatabaseCount('dummies', 4);
        $this->assertDatabaseMissing('entities', $id);
        Artisan::call('sx:import', ['classes' => ['entities']]);
        $this->assertDatabaseCount('entities', 5);
        $this->assertDatabaseCount('dummies', 4);
        $this->assertDatabaseHas('entities', $id);
    }

    public function test_import_command_fresh(): void
    {
        $this->assertDatabaseCount('entities', 4);
        Artisan::call('sx:import', ['--fresh' => true]);
        $this->assertDatabaseCount('entities', 1);
    }

    /* public function test_import_command_output(): void
    {
        $command = $this->artisan('sx:import', ['classes' => ['entities']]);
        $command->expectsOutput('entities: Import triggered.');
    } */
}
