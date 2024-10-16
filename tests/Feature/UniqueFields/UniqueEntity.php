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
     * The indexes to add to the database.
     * 
     * **optional**
     * 
     * Defaults to `[]`.
     */
    public static function indexes(): array
    {
        return [
            'statinternal_2',
            ['statinternal_3', 'statinternal_4']
        ];
    }

    /**
     * The columns that should have a different type than used in sx.
     * 
     * Array should be of the structure $column => $type.
     * 
     * **optional**
     * 
     * Defaults to `[]`.
     */
    public static function databaseCasts(): array
    {
        return ['fake' => 'string'];
    }

    /**
     * Foreign keys to be added to the database.
     * 
     * Array should be of the structure $column => [$table, $foreign].
     * 
     * **optional**
     * 
     * Defaults to `[]`.
     */
    public static function foreignKeys(): array
    {
        return [
            'statinternal_1' => ['entities', 'id']
        ];
    }

    /**
     * The fields that should be unique.
     */
    public static function uniqueFields(): array
    {
        return ['unique_id'];
    }
}
