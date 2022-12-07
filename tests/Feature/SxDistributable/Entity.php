<?php

namespace berthott\SX\Tests\Feature\SxDistributable;

use berthott\SX\Models\Traits\SxDistributable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use SxDistributable;
    use HasFactory;

    /**
     * The class of the sxable to collect.
     */
    public static function sxable(): string
    {
        return EntitySx::class;
    }

    /**
     * An array of background variables to push to sx
     */
    public static function sxBackgroundVariables(self $distributable): array
    {
        return [
            's_2' => 1999,
        ];
    }

    protected static function newFactory()
    {
        return EntityFactory::new();
    }
}
