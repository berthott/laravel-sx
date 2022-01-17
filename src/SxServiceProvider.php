<?php

namespace berthott\SX;

use berthott\SX\Console\Import;
use berthott\SX\Console\Init;
use berthott\SX\Console\Drop;
use berthott\SX\Exceptions\Handler;
use berthott\SX\Facades\Sxable;
use berthott\SX\Helpers\Helpers;
use berthott\SX\Helpers\SxLog as HelpersSxLog;
use berthott\SX\Http\Controllers\SxableController;
use berthott\SX\Http\Middleware\ConvertLabelsToValues;
use berthott\SX\Http\Middleware\ConvertStringBooleans;
use berthott\SX\Models\Contracts\Targetable;
use berthott\SX\Services\Http\SxApiService;
use berthott\SX\Services\Http\SxEntityService;
use berthott\SX\Services\SxableService;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SxServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // bind singletons
        $this->app->singleton('Helpers', function () {
            return new Helpers();
        });
        $this->app->singleton('SxLog', function () {
            return new HelpersSxLog();
        });
        $this->app->singleton('SxHttpService', function () {
            return new SxEntityService();
        });
        $this->app->bind('SxApiService', function () {
            return new SxApiService();
        });
        $this->app->singleton('Sxable', function () {
            return new SxableService();
        });

        // bind exception singleton
        $this->app->singleton(ExceptionHandler::class, Handler::class);

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

        // add middlewares
        $router = app(Router::class);
        $router->aliasMiddleware('sx.string_booleans', ConvertStringBooleans::class);
        $router->aliasMiddleware('sx.labels_to_values', ConvertLabelsToValues::class);

        // add routes
        Route::group($this->routeConfiguration(), function () {
            foreach (Sxable::getSxableClasses() as $sxable) {
                Route::post($sxable::entityTableName().'/import', [SxableController::class, 'import'])->name($sxable::entityTableName().'.import');
                Route::get($sxable::entityTableName().'/export', [SxableController::class, 'export'])->name($sxable::entityTableName().'.export');
                Route::get("{$sxable::entityTableName()}/structure", [SxableController::class, 'structure'])->name($sxable::entityTableName().'.structure');
                Route::get("{$sxable::entityTableName()}/labels", [SxableController::class, 'labels'])->name($sxable::entityTableName().'.labels');
                Route::get("{$sxable::entityTableName()}/{{$sxable::singleName()}}/respondent", [SxableController::class, 'respondent'])->name($sxable::entityTableName().'.respondent');
                Route::apiResource($sxable::entityTableName(), SxableController::class, $sxable::routeOptions());
            }
        });

        // Register the command if we are using the application via the CLI
        if ($this->app->runningInConsole()) {
            $this->commands([
                Import::class,
                Init::class,
                Drop::class,
            ]);
        }

        // Disable Data Wrapping on resources
        JsonResource::withoutWrapping();
    }

    protected function routeConfiguration(): array
    {
        return [
            'middleware' => [...config('sx.middleware'), 'sx.string_booleans', 'sx.labels_to_values'],
            'prefix' => config('sx.prefix'),
        ];
    }
}
