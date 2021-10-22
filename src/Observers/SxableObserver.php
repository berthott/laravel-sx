<?php

namespace berthott\SX\Observers;

use Illuminate\Database\Eloquent\Model;

class SxableObserver
{
    /**
     * Handle the Models "created" event.
     */
    public function creating(Model $model): void
    {
        $a = 0;
    }

    /**
     * Handle the Models "updated" event.
     */
    public function updating(Model $model): void
    {
        $a = 0;
    }

    /**
     * Handle the Models "deleted" event.
     */
    public function deleting(Model $model): void
    {
        $a = 0;
    }
}
