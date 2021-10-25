<?php

namespace berthott\SX\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SxableObserver
{
    /**
     * Handle the Models "created" event.
     */
    public function created(Model $model): void
    {
        $table = $model::longTableName();
        $attributes = $model->getAttributes();
        foreach ($attributes as $variableName => $value) {
            if (in_array($variableName, ['id', config('sx.primary'), 'created_at', 'updated_at'])) {
                continue;
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
            DB::table($table)->insert($entry);
        }
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
