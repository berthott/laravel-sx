<?php

namespace berthott\SX\Tests\Feature\Commands;

use berthott\SX\Models\Traits\Sxable;
use Illuminate\Database\Eloquent\Model;

class Dummy extends Model
{
    use Sxable;
}
