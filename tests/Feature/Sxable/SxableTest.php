<?php

namespace berthott\SX\Tests\Feature\Sxable;

use berthott\SX\Facades\Sx;
use berthott\SX\Facades\Sxable;
use Illuminate\Support\Facades\Schema;

class SxableTest extends SxableTestCase
{
    public function test_sxable_found(): void
    {
        $sxables = Sxable::getSxableClasses();
        $this->assertNotEmpty($sxables);
    }
    
    public function test_entity_table_creation(): void
    {
        Sxable::getSxableClasses();
        $this->assertTrue(Schema::hasTable('entities'));
        $this->assertTrue(Schema::hasTable('entity_values'));
        $this->assertTrue(Schema::hasTable('entity_questions'));
    }
}
