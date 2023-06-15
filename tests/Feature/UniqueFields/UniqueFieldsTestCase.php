<?php

namespace berthott\SX\Tests\Feature\UniqueFields;

use Facades\berthott\SX\Services\Http\SxApiService;
use berthott\SX\SxServiceProvider;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class UniqueFieldsTestCase extends BaseTestCase
{
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
        Config::set('sx.namespace', __NAMESPACE__);
        Config::set('sx.auth', [
            'Syspons_API',
            'SySpons$$'
        ]);
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
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                File::get(__DIR__.'/../dataset.csv'),
            ));
        SxApiService::makePartial();
    }
}
