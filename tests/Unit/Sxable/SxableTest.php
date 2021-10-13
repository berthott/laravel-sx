<?php

namespace berthott\SX\Tests\Unit\Sxable;

use berthott\SX\Facades\Sxable;
use berthott\SX\Facades\SxController;

class SxableTest extends SxableTestCase
{
    public function test_survey(): void
    {
        $sxables = Sxable::getSxableClasses();
        $this->assertNotEmpty($sxables);
    }
}
