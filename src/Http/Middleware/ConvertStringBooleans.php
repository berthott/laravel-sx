<?php

namespace berthott\SX\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

/**
 * Middleware.
 */
class ConvertStringBooleans extends TransformsRequest
{

    /**
     * Transform the given value.
     * 
     * Convert string booleans to actual booleans.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        if ($value === 'true' || $value === 'TRUE') {
            return true;
        }

        if ($value === 'false' || $value === 'FALSE') {
            return false;
        }

        return $value;
    }
}
