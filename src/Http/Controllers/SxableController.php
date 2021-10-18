<?php

namespace berthott\SX\Http\Controllers;

use berthott\SX\Models\Contracts\Targetable;
use berthott\SX\Models\Traits\Targetable as TraitsTargetable;
use Illuminate\Database\Eloquent\Collection;

class SxableController implements Targetable
{
    use TraitsTargetable;

    /**
     * Display a listing of the resource.
     */
    public function index(): Collection
    {
        return $this->target::all();
    }
}
