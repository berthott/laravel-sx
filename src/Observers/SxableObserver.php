<?php

namespace berthott\SX\Observers;

use berthott\SX\Facades\SxLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SxableObserver
{
    /**
     * Handle the Models "created" event.
     */
    public function created(Model $model): void
    {
        DB::table($model::longTableName())->insert($model::makeLongEntries($model->getAttributes()));
        $this->log($model, 'created');
    }

    /**
     * Handle the Models "updated" event.
     */
    public function updated(Model $model): void
    {
        DB::table($model::longTableName())
                ->upsert($model::makeLongEntries($model->getAttributes()), [
                    'respondent_id',
                    'variableName',
                ]);
        $this->log($model, 'upserted');
    }

    /**
     * Handle the Models "deleted" event.
     */
    public function deleted(Model $model): void
    {
        DB::table($model::longTableName())
                ->where('respondent_id', $model->{config('sx.primary')})
                ->delete();
        $this->log($model, 'deleted');
    }

    private function log(Model $model, string $action)
    {
        //SxLog::log($model->entityTableName().' '.config('sx.primary').' '.$model[config('sx.primary')].': Long table entries '.$action.'.');
    }
}
