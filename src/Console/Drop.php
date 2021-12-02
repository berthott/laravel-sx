<?php

namespace berthott\SX\Console;

use berthott\SX\Facades\Sxable;
use berthott\SX\Facades\SxLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Drop extends Command
{
    protected $signature = 'sx:drop {classes?*}';
    protected $description = 'Drop SX tables';

    public function handle()
    {
        // intercept logging and additionally output to console
        SxLog::shouldReceive('log')
            ->andReturnUsing(function ($message) {
                $this->line($message);
                Log::channel('surveyxact')->info($message);
            });
    
        foreach (Sxable::getSxableClasses() as $class) {
            if ($this->argument('classes') && !in_array($class::entityTableName(), $this->argument('classes'))) {
                continue;
            }
            $class::dropTables();
        }
    }
}
