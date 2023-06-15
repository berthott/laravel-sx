<?php

namespace berthott\SX\Services;

use berthott\SX\Models\Traits\Sxable;
use berthott\Targetable\Services\TargetableService;

/**
 * TargetableService implementation for an sxable class.
 * 
 * @link https://docs.syspons-dev.com/laravel-targetable
 */
class SxableService extends TargetableService
{
    public function __construct()
    {
        parent::__construct(Sxable::class, 'sx');
    }
}
