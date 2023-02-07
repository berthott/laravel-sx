<?php

namespace berthott\SX\Tests\Feature\MultiLanguage;

use berthott\SX\Models\Traits\Sxable;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use Sxable;

    /**
     * The languages the survey covers. First one is the default language.
     */
    public static function surveyLanguages(): array
    {
        return ['de', 'en'];
    }
}
