<?php

namespace berthott\SX\Facades;

use Illuminate\Support\Facades\Facade;

class SxLog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'SxLog';
    }
}
