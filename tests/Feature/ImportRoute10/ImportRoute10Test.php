<?php

namespace berthott\SX\Tests\Feature\ImportRoute10;

class ImportRoute10Test extends ImportRoute10TestCase
{
    public function test_import_10_entities(): void
    {
        $this->post(route('entities.sync', [ 'labeled' => true ]))
            ->assertStatus(200);
        $this->assertDatabaseCount('entities', 10);
    }
}
