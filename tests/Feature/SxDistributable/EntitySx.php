<?php

namespace berthott\SX\Tests\Feature\SxDistributable;

use berthott\SX\Models\Traits\Sxable;
use Illuminate\Database\Eloquent\Model;

class EntitySx extends Model
{
    use Sxable;
}
