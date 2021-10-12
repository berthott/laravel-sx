<?php

namespace berthott\SX\Tests\Unit;

use berthott\SX\SxServiceProvider;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class ApiTestCase extends BaseTestCase
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
        Config::set('sx.auth', [
            'Syspons_API',
            'SySpons$$'
        ]);
    }
}
