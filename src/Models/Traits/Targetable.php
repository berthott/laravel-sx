<?php

namespace berthott\SX\Models\Traits;

use berthott\SX\Facades\Sxable;

trait Targetable
{
    /**
     * The target model.
     */
    private string $target;

    public function initTarget(): void
    {
        $this->target = Sxable::getTarget();
    }
}
