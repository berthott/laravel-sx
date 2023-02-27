<?php

namespace berthott\SX\Tests\Feature\RouteOptions;

use berthott\SX\Models\Traits\Sxable;
use Illuminate\Database\Eloquent\Model;

class OnlyEntity extends Model
{
    use Sxable;

    /**
     * Returns an array of route options.
     * See Route::apiResource documentation.
     */
    public static function routeOptions(): array
    {
        return [
            'only' => ['index']
        ];
    }
}
