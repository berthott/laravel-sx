<?php

namespace berthott\SX\Facades;

use Illuminate\Support\Facades\Facade;

class SxDistributable extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'SxDistributable';
    }
}
