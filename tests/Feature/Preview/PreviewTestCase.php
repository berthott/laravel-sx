<?php

namespace berthott\SX\Tests\Feature\Preview;

use berthott\InternalRequest\InternalRequestServiceProvider;
use Facades\berthott\SX\Services\Http\SxApiService;
use berthott\SX\SxServiceProvider;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema as FacadesSchema;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class PreviewTestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('sx:init');
    }

    protected function getPackageProviders($app)
    {
        return [
            SxServiceProvider::class,
            InternalRequestServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->setUpDistributionEntityTable();
        date_default_timezone_set('Europe/Berlin');
        Config::set('app.timezone', 'Europe/Berlin');
        Config::set('sx.namespace', __NAMESPACE__);
        Config::set('sx-distribution.namespace', __NAMESPACE__);
        Config::set('sx.auth', [
            'Syspons_API',
            'dL1lty$KWj61'
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
        SxApiService::shouldReceive('create')
            ->andReturn(new Response(
                $status = 200,
                $headers = [],
                File::get(__DIR__.'/../841931211.xml'),
            ));
        SxApiService::makePartial();
    }

    private function setUpDistributionEntityTable(): void
    {
        FacadesSchema::create('distribution_entities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('year');
            $table->timestamps();
        });
    }
}
