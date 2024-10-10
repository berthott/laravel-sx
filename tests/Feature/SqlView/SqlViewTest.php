<?php

namespace berthott\SX\Tests\Feature\SqlView;

class SqlViewTest extends SqlViewTestCase
{
    public function test_view(): void
    {
        $this->assertDatabaseCount('entities_long', 4 * 108);
        $this->assertDatabaseCount('entities_report', 4);
    }
}
