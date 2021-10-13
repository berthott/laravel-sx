<?php

namespace berthott\SX\Models\Traits;

use berthott\SX\Models\SxTableFormat;

trait Sxable
{
    /**
     * The Survey Id that should be connected to this Model.
     */
    public static function surveyId(): string
    {
        return '';
    }

    /**
     * The format in which the data should be stored
     */
    public static function format(): string
    {
        return SxTableFormat::long;
    }
}
