<?php

namespace berthott\SX\Tests\Feature\UniqueFields;

use berthott\SX\Models\Traits\Sxable;
use Illuminate\Database\Eloquent\Model;

class GeneratedUniqueEntity extends Model
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
     * Defines unique fields with 'field' => 'key'.
     * The key defines a string that is appended by a number.
     */
    public static function generatedUniqueFields(): array
    {
        return ['generated_id' => 'GEN'];
    }
}
