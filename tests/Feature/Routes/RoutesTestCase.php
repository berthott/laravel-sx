<?php

namespace berthott\SX\Tests\Feature\Routes;

use berthott\SX\Facades\SxApiService;
use berthott\SX\SxServiceProvider;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class RoutesTestCase extends BaseTestCase
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
        date_default_timezone_set('Europe/Berlin');
        Config::set('app.timezone', 'Europe/Berlin');
        Config::set('sx.namespace', __NAMESPACE__);
        Config::set('sx.auth', [
            'Syspons_API',
            'SySpons$$'
        ]);
    }
}
