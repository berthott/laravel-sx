<?php

namespace berthott\SX;

use berthott\SX\Models\Contracts\Targetable;
use berthott\SX\Services\SxableService;
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
        $this->app->singleton('Sxable', function () {
            return new SxableService();
        });

        // bind exception singleton
        //$this->app->singleton(ExceptionHandler::class, Handler::class);

        // add config
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'sx');

        // init targetables
        $this->app->afterResolving(Targetable::class, function (Targetable $targetable) {
            $targetable->initTarget();
        });
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

    protected function routeConfiguration(): array
    {
        return [
            'middleware' => config('sx.middleware'),
            'prefix' => config('sx.prefix'),
        ];
    }
}
