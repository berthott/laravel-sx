<?php

namespace berthott\SX\Tests\Feature\UniqueFields;

use berthott\SX\Models\Traits\Sxable;
use Illuminate\Database\Eloquent\Model;

class UniqueEntity extends Model
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
     * The fields that should be unique.
     */
    public static function uniqueFields(): array
    {
        return ['unique_id'];
    }
}
