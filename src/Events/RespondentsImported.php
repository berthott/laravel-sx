<?php

namespace berthott\SX\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event to be dispatched whenever new entities where imported.
 * 
 * Can be used to trigger actions within the host application. To
 * do so add an entry to your Laravel applications `EventServiceProvider`:
 * 
 * ```php
 * protected $listen = [
 *      RespondentsImported::class => [
 *          // an array of listeners, e.g.
 *          FlushApiCache::class,
 *      ],
 *  ];
 * ```
 */
class RespondentsImported
{
    use Dispatchable;

    public function __construct(public string $model) {}
}