<?php

namespace berthott\SX\Facades;

use Illuminate\Support\Facades\Facade;

class Sx extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Sx';
    }
}
