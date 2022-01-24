<?php

namespace berthott\SX\Tests\Feature\ImportRoute;

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
}
