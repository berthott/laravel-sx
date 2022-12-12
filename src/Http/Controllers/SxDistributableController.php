<?php

namespace berthott\SX\Http\Controllers;

use berthott\SX\Facades\SxDistributable;
use Illuminate\Support\Facades\Redirect;

class SxDistributableController
{
    private string $target;

    public function __construct()
    {
        $this->target = SxDistributable::getTarget();
    }

    /**
     * Collect the target.
     */
    public function sxcollect(mixed $id)
    {
        return Redirect::to($this->target::findOrFail($id)->collect()->collecturl());
    }
}
