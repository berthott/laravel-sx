<?php

namespace berthott\SX\Tests\Feature\Include;

use berthott\SX\Models\Traits\Sxable;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use Sxable;

    /**
     * The fields that should be excluded from being processed.
     * Will be ignored when include is set
     */
    public static function exclude(): array
    {
        return ['survey', 'responde'];
    }

    /**
     * The fields that should be processed.
     */
    public static function include(): array
    {
        return ['survey', 'responde'];
    }
}
