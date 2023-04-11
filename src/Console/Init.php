<?php

namespace berthott\SX\Console;

use berthott\SX\Facades\Sxable;
use berthott\SX\Facades\SxLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Init extends Command
{
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

        foreach (Sxable::getTargetableClasses() as $class) {
            if ($this->argument('classes') && !in_array($class::entityTableName(), $this->argument('classes'))) {
                continue;
            }
            $class::initTables(force: $this->option('fresh'), labeled: false, max: $this->option('max'));
        }
    }
}
