<?php

namespace berthott\SX\Facades;

use Illuminate\Support\Facades\Facade;

class SxApiService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'SxApiService';
    }
}
