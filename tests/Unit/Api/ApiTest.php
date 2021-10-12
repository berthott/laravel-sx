<?php

namespace berthott\SX\Tests\Unit\Api;

use berthott\SX\Facades\SxController;
use berthott\SX\Tests\Unit\ApiTestCase;

class Api extends ApiTestCase
{
    public function test_survey(): void
    {
        $response = SxController::survey()->get([
            'survey' => '1325978'
        ]);
        $this->assertEquals($response->getStatusCode(), 200);
    }
}
