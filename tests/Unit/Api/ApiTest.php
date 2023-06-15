<?php

namespace berthott\SX\Tests\Unit\Api;

use Facades\berthott\SX\Services\Http\SxHttpService;

class ApiTest extends ApiTestCase
{
    public function test_survey(): void
    {
        $response = SxHttpService::surveys()->get([
            'survey' => '1325978'
        ]);
        $this->assertEquals($response->getStatusCode(), 200);
    }
}
