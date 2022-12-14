<?php

namespace berthott\SX\Tests\Feature\SxDistributable;

use berthott\SX\Models\Traits\SxDistributable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Entity extends Model
{
    use SxDistributable;
    use HasFactory;

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

    /**
     * Return the data for the sx survey.
     */
    public function sxData(): array
    {
        return [
            'name' => $this->name,
        ];
    }

    protected static function newFactory()
    {
        return EntityFactory::new();
    }
}
