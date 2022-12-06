<?php

namespace berthott\SX\Http\Controllers;

use berthott\SX\Facades\SxDistributable;

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
        return $this->target::findOrFail($id)->collect();
    }
}
