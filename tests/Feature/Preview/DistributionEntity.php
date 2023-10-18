<?php

namespace berthott\SX\Tests\Feature\Preview;

use berthott\SX\Models\Traits\SxDistributable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DistributionEntity extends Model
{
    use SxDistributable;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * The single name of the model.
     */
    public static function singleName(): string
    {
        return Str::snake(class_basename(get_called_class()));
    }

    /**
     * The entity table name of the model.
     */
    public static function entityTableName(): string
    {
        return Str::snake(Str::pluralStudly(class_basename(get_called_class())));
    }

    /**
     * The class of the sxable to collect.
     */
    public static function sxable(): string
    {
        return Entity::class;
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
}
