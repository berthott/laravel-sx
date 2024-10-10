<?php

namespace berthott\SX\Tests\Feature\SqlView;

use berthott\SX\Models\Traits\Sxable;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use Sxable;

    public static function views(): array
    {
        return [
            'report' => "
                SELECT * FROM entities_long
                WHERE variableName = 'fake'
            "
        ];
    }
}
