<?php

namespace berthott\SX\Tests\Feature\SxDistributable;

use berthott\SX\Models\Traits\SxDistributable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use SxDistributable;
    use HasFactory;

    protected static function newFactory()
    {
        return EntityFactory::new();
    }
}
