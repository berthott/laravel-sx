<?php

namespace berthott\SX\Models\Traits;

use berthott\SX\Models\Respondent;
use Illuminate\Support\Str;

trait SxDistributable
{
    /**
     * The single name of the model.
     */
    public static function singleName(): string
    {
        return Str::snake(class_basename(get_called_class()));
    }

    /**
     * The entity table name of the model.
     */
    public static function entityTableName(): string
    {
        return Str::snake(Str::pluralStudly(class_basename(get_called_class())));
    }

    /**
     * The class of the sxable to collect.
     */
    public static function sxable(): string
    {
        return '';
    }

    public function collect(): Respondent
    {
        return '';
    }
}
