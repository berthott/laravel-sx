<?php

namespace berthott\SX\Console;

use berthott\SX\Facades\Sxable;
use Facades\berthott\SX\Helpers\SxLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Drop extends Command
{
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

        foreach (Sxable::getTargetableClasses() as $class) {
            if ($this->argument('classes') && !in_array($class::entityTableName(), $this->argument('classes'))) {
                continue;
            }
            $class::dropTables();
        }
    }
}
