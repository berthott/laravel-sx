<?php

namespace berthott\SX\Tests\Feature\Routes;

use berthott\SX\Facades\SxApiService;
use berthott\SX\SxServiceProvider;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class RoutesTestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
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
        Config::set('sx.auth', [
            'Syspons_API',
            'SySpons$$'
        ]);
        SxApiService::shouldReceive('exportStructure')
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                File::get(__DIR__.'/../structure.csv'),
            ));
        SxApiService::shouldReceive('exportLabels')
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                File::get(__DIR__.'/../labels.csv'),
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
                )
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