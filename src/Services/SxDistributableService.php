<?php

namespace berthott\SX\Services;

use berthott\SX\Models\Traits\SxDistributable;
use berthott\Targetable\Services\TargetableService;

class SxDistributableService extends TargetableService
{
    /**
     * The Constructor.
     */
    public function __construct()
    {
        parent::__construct(SxDistributable::class, 'sx-distribution');
    }
}
