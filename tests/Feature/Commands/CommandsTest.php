<?php

namespace berthott\SX\Tests\Feature\Commands;

use Illuminate\Support\Facades\Artisan;

class CommandsTest extends CommandsTestCase
{
    public function test_import_command(): void
    {
        $id = ['responde' => 841931211];
        $this->assertDatabaseCount('entities', 4);
        $this->assertDatabaseCount('dummies', 4);
        $this->assertDatabaseMissing('entities', $id);
        Artisan::call('import');
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
        Artisan::call('import', ['classes' => ['entities']]);
        $this->assertDatabaseCount('entities', 5);
        $this->assertDatabaseCount('dummies', 4);
        $this->assertDatabaseHas('entities', $id);
    }

    public function test_import_command_fresh(): void
    {
        $this->assertDatabaseCount('entities', 4);
        Artisan::call('import', ['--fresh' => true]);
        $this->assertDatabaseCount('entities', 1);
    }

    public function test_import_command_output(): void
    {
        $command = $this->artisan('import', ['classes' => ['entities']]);
        $command->expectsOutput('entities: Import triggered.');
    }
}
