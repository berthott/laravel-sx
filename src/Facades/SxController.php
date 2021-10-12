<?php

namespace berthott\SX\Facades;

use Illuminate\Support\Facades\Facade;

class SxController extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'SxController';
    }
}
