<?php

namespace berthott\SX\Facades;

use Illuminate\Support\Facades\Facade;

class SxReportPdf extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'SxReportPdf';
    }
}
