<?php

namespace berthott\SX\Console;

use Facades\berthott\SX\Services\SxableService;
use Facades\berthott\SX\Helpers\SxLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * An artisan command to drop all SX tables.
 * 
 * * Takes a list of `classes` to be applied to. If non is provided,
 * all SX tables are dropped.
 * * Takes a `memory` option to set the memory limit.
 * 
 * @api
 */
class Drop extends Command
{
    /**
     * The Signature.
     * 
     * @api
     */
    protected $signature = 'sx:drop {classes?*} {--memory=}';
    protected $description = 'Drop SX tables';

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

        foreach (SxableService::getTargetableClasses() as $class) {
            if ($this->argument('classes') && !in_array($class::entityTableName(), $this->argument('classes'))) {
                continue;
            }
            $class::dropTables();
        }
    }
}
