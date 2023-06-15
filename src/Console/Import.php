<?php

namespace berthott\SX\Console;

use Facades\berthott\SX\Services\SxableService;
use Facades\berthott\SX\Helpers\SxLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Import extends Command
{
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
