<?php

namespace berthott\SX\Console;

use Facades\berthott\SX\Services\SxableService;
use Facades\berthott\SX\Helpers\SxLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * An artisan command to import SX data.
 * 
 * * Takes a list of `classes` to be applied to. If non is provided,
 * all SX classes are imported.
 * * Takes a `fresh` option to drop all previous imported data.
 * * Takes a `since` option to define a timestamp from which onward 
 * data should be imported.
 * * Takes a `memory` option to set the memory limit.
 * 
 * @api
 */
class Import extends Command
{
    /**
     * The Signature.
     * 
     * @api
     */
    protected $signature = 'sx:import {classes?*} {--fresh} {--since=} {--memory=}';
    protected $description = 'Import SX data';

    public function handle()
    {
        // set memory limit
        if ($this->option('memory')) {
            ini_set('memory_limit', $this->option('memory'));
        }

        // intercept logging and additionally output to console
        SxLog::shouldReceive('log')
            ->andReturnUsing(function ($message) {
                $this->line($message);
                Log::channel('surveyxact')->info($message);
            });

        // Fixes exceeded memory usage
        if ($this->option('fresh')) {
            DB::connection()->unsetEventDispatcher();
            DB::disableQueryLog();
        }

        foreach (SxableService::getTargetableClasses() as $class) {
            if ($this->argument('classes') && !in_array($class::entityTableName(), $this->argument('classes'))) {
                continue;
            }
            $class::import(true, $this->option('fresh'), $this->option('since'));
        }
    }
}
