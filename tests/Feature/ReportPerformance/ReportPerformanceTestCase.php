<?php

namespace berthott\SX\Tests\Feature\ReportPerformance;

use Facades\berthott\SX\Services\Http\SxApiService;
use berthott\SX\SxServiceProvider;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class ReportPerformanceTestCase extends BaseTestCase
{
    protected $now;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('sx:init');
    }

    protected function getPackageProviders($app)
    {
        return [
            SxServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->now = now();
        Carbon::setTestNow($this->now);
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
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                File::get(__DIR__.'/../dataset_50000.csv'),
            ));
        SxApiService::makePartial();
    }
}
