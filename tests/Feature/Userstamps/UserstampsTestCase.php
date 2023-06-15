<?php

namespace berthott\SX\Tests\Feature\Userstamps;

use Facades\berthott\SX\Services\Http\SxApiService;
use berthott\SX\SxServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class UserstampsTestCase extends BaseTestCase
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
        $this->setUpUserTable();
        Config::set('sx.namespace', __NAMESPACE__);
        Config::set('sx.auth', [
            'Syspons_API',
            'SySpons$$'
        ]);
    }

    private function setUpUserTable(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('remember_token')->nullable();
            $table->timestamps();
        });
    }
}
