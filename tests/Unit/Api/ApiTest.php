<?php

namespace berthott\SX\Tests\Unit\Api;

use berthott\SX\Facades\SxController;

class ApiTest extends ApiTestCase
{
    public function test_survey(): void
    {
        $response = SxController::surveys()->get([
            'survey' => '1325978'
        ]);
        $this->assertEquals($response->getStatusCode(), 200);
    }
}
