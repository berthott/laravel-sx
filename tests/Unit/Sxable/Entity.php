<?php

namespace berthott\SX\Tests\Unit\Sxable;

use berthott\SX\Models\Traits\Sxable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use Sxable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The Survey Id that should be connected to this Model.
     */
    public static function surveyId(): string
    {
        return '1325978';
    }

    protected static function newFactory()
    {
        return EntityFactory::new();
    }
}
