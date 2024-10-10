<?php

namespace berthott\SX\Tests\Feature\DummySeeding;

use Illuminate\Support\Facades\DB;

class DummySeedingTest extends DummySeedingTestCase
{
    public function test_truncate(): void
    {
        $this->assertDatabaseCount('entities', 4);
        Entity::truncateData(11);
        $this->assertDatabaseCount('entities', 0);
    }

    public function test_dummy_seeding(): void
    {
        Entity::seedDummyData(11);
        $this->assertDatabaseCount('entities', 11);
        $this->assertDatabaseCount('entities_long', 11 * 108);
    }

    public function test_dummy_seeding_with_override(): void
    {
        $teststring = 'test@syspons.com';
        Entity::seedDummyData(11, ['s_1' => $teststring]);
        $this->assertEquals(11, DB::table('entities')->where('s_1', $teststring)->count());
        $this->assertEquals(11, DB::table('entities_long')->where('variableName', 's_1')->where('value_string', $teststring)->count());
    }
}
