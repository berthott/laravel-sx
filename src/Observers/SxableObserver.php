<?php

namespace berthott\SX\Observers;

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
        $this->makeLongEntry($model, function ($entry) use ($model) {
            DB::table($model::longTableName())->insert($entry);
        });
    }

    /**
     * Handle the Models "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->makeLongEntry($model, function ($entry) use ($model) {
            DB::table($model::longTableName())
                ->updateOrInsert([
                    'respondent_id' => $entry['respondent_id'],
                    'variableName' => $entry['variableName'],
                ], $entry);
        });
    }

    /**
     * Handle the Models "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->makeLongEntry($model, function ($entry) use ($model) {
            DB::table($model::longTableName())
                ->where('respondent_id', $entry['respondent_id'])
                ->where('variableName', $entry['variableName'])
                ->delete();
        });
    }

    /**
     * Handle the Models "deleted" event.
     */
    private function makeLongEntry(Model $model, Closure $callback): void
    {
        $attributes = $model->getAttributes();
        foreach ($attributes as $variableName => $value) {
            if (in_array($variableName, ['id', config('sx.primary'), 'created_at', 'updated_at'])) {
                continue;
            }
            if ($variableName === 's_2') {
                $a = 0;
            }
            $type = DB::getSchemaBuilder()->getColumnType($model::entityTableName(), $variableName);
            $entry = [
                'respondent_id' => $attributes[config('sx.primary')],
                'variableName' => $variableName,
                'created_at' => $attributes['created_at'],
                'updated_at' => $attributes['updated_at'],
            ];
            switch ($type) {
                case 'integer':
                    $entry['value_single_multiple'] = $value;
                    break;
                case 'string':
                    $entry['value_string'] = $value;
                    break;
                case 'float':
                    $entry['value_double'] = $value;
                    break;
                case 'datetime':
                    $entry['value_datetime'] = $value;
                    break;
            }
            $callback($entry);
        }
    }
}
