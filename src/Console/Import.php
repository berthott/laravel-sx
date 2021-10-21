<?php

namespace berthott\SX\Console;

use berthott\SX\Facades\Sxable;
use Illuminate\Console\Command;

class Import extends Command
{
    protected $signature = 'import {classes?*} {--fresh}';
    protected $description = 'Import SX data';

    public function handle()
    {
        foreach (Sxable::getSxableClasses() as $class) {
            if ($this->argument('classes') && !in_array($class::entityTableName(), $this->argument('classes'))) {
                continue;
            }
            $this->option('fresh')
              ? $class::initTables(force: true)
              : $class::import();
        }
    }
}
