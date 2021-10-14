<?php

namespace berthott\SX\Tests\Feature\Sxable;

use berthott\SX\Facades\Sx;
use berthott\SX\SxServiceProvider;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class SxableTestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Sx::shouldReceive('getStructureFromApi')
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                File::get(__DIR__.'/structure.csv'),
            ));
        Sx::makePartial();
    }

    protected function getPackageProviders($app)
    {
        return [
            SxServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        Config::set('sx.namespace', __NAMESPACE__);
    }
}
