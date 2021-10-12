<?php

namespace berthott\SX;

use berthott\SX\Services\SxEntityService;
use Illuminate\Support\ServiceProvider;

class SxServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // bind singletons
        $this->app->singleton('SxController', function () {
            return new SxEntityService();
        });

        // bind exception singleton
        //$this->app->singleton(ExceptionHandler::class, Handler::class);

        // add config
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'sx');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // publish config
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('sx.php'),
        ], 'config');

        // register log channel
        $this->app->make('config')->set('logging.channels.surveyxact', [
            'driver' => 'daily',
            'path' => storage_path('logs/surveyxact.log'),
            'level' => 'debug',
        ]);
    }
}
