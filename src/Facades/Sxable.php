<?php

namespace berthott\SX\Facades;

use Illuminate\Support\Facades\Facade;

class Sxable extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Sxable';
    }
}
