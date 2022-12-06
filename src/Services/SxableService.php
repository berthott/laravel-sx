<?php

namespace berthott\SX\Services;

use berthott\SX\Models\Traits\Sxable;
use berthott\Targetable\Services\TargetableService;

class SxableService extends TargetableService
{
    /**
     * The Constructor.
     */
    public function __construct()
    {
        parent::__construct(Sxable::class, 'sx');
    }
}
