<?php

namespace berthott\SX\Tests\Feature\Routes;

use Facades\berthott\SX\Services\Http\SxApiService;
use berthott\SX\SxServiceProvider;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class RoutesMockedTestCase extends BaseTestCase
{
    protected string $now;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('sx:init');
    }

    protected function getPackageProviders($app)
    {
        return [
            SxServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->now = now()->format('Y-m-d H:i:s');
        Config::set('sx.namespace', __NAMESPACE__);
        SxApiService::shouldReceive('exportStructure')
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                File::get(__DIR__.'/../structure_de.csv'),
            ));
        SxApiService::shouldReceive('exportLabels')
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                File::get(__DIR__.'/../labels_de.csv'),
            ));
        SxApiService::shouldReceive('exportDataset')
            ->andReturn(
                new Response(
                    $status = 200,
                    $headers = [],
                    File::get(__DIR__.'/../dataset.csv'),
                ),
                new Response(
                    $status = 200,
                    $headers = [],
                    File::get(__DIR__.'/../import.csv'),
                ),
                new Response(
                    $status = 200,
                    $headers = [],
                    File::get(__DIR__.'/../update.csv'),
                ),
            );
        SxApiService::shouldReceive('get')
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                File::get(__DIR__.'/../825478429.xml'),
            ));
        SxApiService::makePartial();
    }
}
