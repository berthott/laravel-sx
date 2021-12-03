<?php

namespace berthott\SX\Tests\Feature\Exclude;

use berthott\SX\Models\Traits\Sxable;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use Sxable;

    /**
     * The Survey Id that should be connected to this Model.
     */
    public static function surveyId(): string
    {
        return '1325978';
    }

    /**
     * The fields that should be excluded from being processed.
     * Will be ignored when include is set
     */
    public static function exclude(): array
    {
        return ['survey', 'organiza', 'email'];
    }
}
