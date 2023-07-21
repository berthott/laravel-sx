<?php

namespace berthott\SX\Tests\Feature\ImportRoute10;

use berthott\SX\SxServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class ImportRoute10TestCase extends BaseTestCase
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
        Config::set('sx.namespace', __NAMESPACE__);
        Config::set('sx.auth', [
            'Syspons_API',
            'dL1lty$KWj61'
        ]);
    }
}
