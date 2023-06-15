<?php

namespace berthott\SX\Services;

use berthott\SX\Models\Traits\SxDistributable;
use berthott\Targetable\Services\TargetableService;

/**
 * TargetableService implementation for a sx-distributable class.
 * 
 * @link https://docs.syspons-dev.com/laravel-targetable
 */
class SxDistributableService extends TargetableService
{
    public function __construct()
    {
        parent::__construct(SxDistributable::class, 'sx-distribution');
    }
}
