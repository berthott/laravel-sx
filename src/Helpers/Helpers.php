<?php

namespace berthott\SX\Helpers;

use Illuminate\Support\Collection;

class Helpers
{
    /**
     * Get the values for the values table.
     */
    public function pluckFromCollection(Collection $collection, string ...$args): Collection
    {
        return $collection->map(function ($item) use ($args) {
            return array_intersect_key($item, array_fill_keys($args, ''));
        });
    }
}
