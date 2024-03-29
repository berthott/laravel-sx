<?php

namespace berthott\SX\Console;

use Facades\berthott\SX\Services\SxableService;
use Facades\berthott\SX\Helpers\SxLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * An artisan command to init SX tables and import data.
 * 
 * * Takes a list of `classes` to be applied to. If non is provided,
 * all possible SX tables are created and imported.
 * * Takes a `fresh` option to drop all previous created tables.
 * * Takes a `max` option to define a maximum of data entries to be
 * imported per table.
 * * Takes a `memory` option to set the memory limit.
 * 
 * @api
 */
class Init extends Command
{
    /**
     * The Signature.
     * 
     * @api
     */
    protected $signature = 'sx:init {classes?*} {--fresh} {--max=} {--memory=}';
    protected $description = 'Initialize SX tables';

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
            $class::initTables(force: $this->option('fresh'), labeled: false, max: $this->option('max'));
        }
    }
}
