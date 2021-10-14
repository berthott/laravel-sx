<?php

namespace berthott\SX\Facades;

use Illuminate\Support\Facades\Facade;

class SxHttpService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'SxHttpService';
    }
}
