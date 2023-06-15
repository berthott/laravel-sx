<?php

namespace berthott\SX;

use berthott\SX\Console\Import;
use berthott\SX\Console\Init;
use berthott\SX\Console\Drop;
use berthott\SX\Exceptions\Handler;
use berthott\SX\Http\Controllers\SxableController;
use berthott\SX\Http\Controllers\SxDistributableController;
use berthott\SX\Http\Middleware\ConvertLabelsToValues;
use berthott\SX\Http\Middleware\ConvertStringBooleans;
use Facades\berthott\SX\Services\SxableService;
use Facades\berthott\SX\Services\SxDistributableService;
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
        // bind exception singleton
        $this->app->singleton(ExceptionHandler::class, Handler::class);

        // add config
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'sx');
        $this->mergeConfigFrom(__DIR__.'/../config/distribution.php', 'sx-distribution');
        $this->mergeConfigFrom(__DIR__.'/../config/query-builder.php', 'query-builder');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // publish config
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('sx.php'),
            __DIR__.'/../config/distribution.php' => config_path('sx-distribution.php'),
        ], 'config');

        // register log channel
        $this->app->make('config')->set('logging.channels.surveyxact', [
            'driver' => 'daily',
            'path' => storage_path('logs/surveyxact.log'),
            'level' => 'debug',
        ]);

        // load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sx');

        // publish view
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/sx'),
        ], 'views');

        // add middlewares
        $router = app(Router::class);
        $router->aliasMiddleware('sx.string_booleans', ConvertStringBooleans::class);
        $router->aliasMiddleware('sx.labels_to_values', ConvertLabelsToValues::class);

        // add routes
        Route::group($this->routeConfiguration('sx', [
            'sx.string_booleans',
            'sx.labels_to_values'
        ]), function () {
            foreach (SxableService::getTargetableClasses() as $sxable) {
                $crudRoutes = $this->getCrudRoutes($sxable::routeOptions());
                if (in_array('sync', $crudRoutes)) Route::post($sxable::entityTableName().'/sync', [SxableController::class, 'sync'])->name($sxable::entityTableName().'.sync');
                if (in_array('export', $crudRoutes)) Route::match(['get', 'post'], $sxable::entityTableName().'/export', [SxableController::class, 'export'])->name($sxable::entityTableName().'.export');
                if (in_array('structure', $crudRoutes)) Route::get("{$sxable::entityTableName()}/structure", [SxableController::class, 'structure'])->name($sxable::entityTableName().'.structure');
                if (in_array('labels', $crudRoutes)) Route::get("{$sxable::entityTableName()}/labels", [SxableController::class, 'labels'])->name($sxable::entityTableName().'.labels');
                if (in_array('destroy_many', $crudRoutes)) Route::delete("{$sxable::entityTableName()}/destroy_many", [SxableController::class, 'destroy_many'])->name($sxable::entityTableName().'.destroy_many');
                if (in_array('show_respondent', $crudRoutes)) Route::get("{$sxable::entityTableName()}/{{$sxable::singleName()}}/show_respondent", [SxableController::class, 'show_respondent'])->name($sxable::entityTableName().'.show_respondent');
                if (in_array('report', $crudRoutes)) Route::get("{$sxable::entityTableName()}/report", [SxableController::class, 'report'])->name($sxable::entityTableName().'.report');
                if (in_array('languages', $crudRoutes)) Route::get("{$sxable::entityTableName()}/languages", [SxableController::class, 'languages'])->name($sxable::entityTableName().'.languages');
                if (in_array('report_pdf', $crudRoutes)) Route::post("{$sxable::entityTableName()}/report_pdf", [SxableController::class, 'report_pdf'])->name($sxable::entityTableName().'.report_pdf');
                //Route::apiResource($sxable::entityTableName(), SxableController::class, $sxable::routeOptions());

                if (in_array('index', $crudRoutes)) Route::get($sxable::entityTableName(), [SxableController::class, 'index'])->name($sxable::entityTableName().'.index');
                if (in_array('show', $crudRoutes)) Route::get("{$sxable::entityTableName()}/{{$sxable::singleName()}}", [SxableController::class, 'show'])->name($sxable::entityTableName().'.show');
                if (in_array('create_respondent', $crudRoutes)) Route::post($sxable::entityTableName(), [SxableController::class, 'create_respondent'])->name($sxable::entityTableName().'.create_respondent');
                if (in_array('update_respondent', $crudRoutes)) Route::put("{$sxable::entityTableName()}/{{$sxable::singleName()}}", [SxableController::class, 'update_respondent'])->name($sxable::entityTableName().'.update_respondent');
                if (in_array('destroy', $crudRoutes)) Route::delete("{$sxable::entityTableName()}/{{$sxable::singleName()}}", [SxableController::class, 'destroy'])->name($sxable::entityTableName().'.destroy');
            }
        });

        Route::group($this->routeConfiguration('sx-distribution'), function () {
            foreach (SxDistributableService::getTargetableClasses('sx-distribution') as $distributable) {
                Route::get("{$distributable::entityTableName()}/{{$distributable::singleName()}}", [SxDistributableController::class, 'sxcollect'])->name($distributable::entityTableName().'.sxcollect');
                Route::get("{$distributable::entityTableName()}/{{$distributable::singleName()}}/qrcode", [SxDistributableController::class, 'qrcode'])->name($distributable::entityTableName().'.qrcode');
                Route::get("{$distributable::entityTableName()}/{{$distributable::singleName()}}/pdf", [SxDistributableController::class, 'pdf'])->name($distributable::entityTableName().'.pdf');
                Route::get("{$distributable::entityTableName()}/{{$distributable::singleName()}}/sxdata", [SxDistributableController::class, 'sxdata'])->name($distributable::entityTableName().'.sxdata');
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

    protected function routeConfiguration(string $key, array $additionalMiddleware = []): array
    {
        return [
            'middleware' => [...config("$key.middleware"), ...$additionalMiddleware],
            'prefix' => config("$key.prefix"),
        ];
    }

    /**
     * Get the applicable resource methods.
     */
    protected function getCrudRoutes(array $options): array
    {
        $methods = [
            'index', 
            'show', 
            'show_respondent',
            'create_respondent', 
            'update_respondent', 
            'destroy', 
            'destroy_many', 
            'structure', 
            'sync', 
            'export', 
            'labels', 
            'report', 
            'report_pdf', 
            'languages',
        ];

        if (isset($options['only'])) {
            $methods = array_intersect($methods, (array) $options['only']);
        }

        if (isset($options['except'])) {
            $methods = array_diff($methods, (array) $options['except']);
        }

        return $methods;
    }
}
