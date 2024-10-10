<?php

namespace berthott\SX\Helpers;

use Closure;
use Exception;
use Facades\berthott\SX\Helpers\SxLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Grammars\MySqlGrammar;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Some helper functions.
 */
class SxHelpers
{
    /**
     * Pluck a set of keys from a collection.
     */
    public function pluckFromCollection(Collection $collection, string ...$args): Collection
    {
        return $collection->map(function ($item) use ($args) {
            return array_intersect_key($item, array_fill_keys($args, ''));
        });
    }

    /**
     * Get a labeled resource.
     * 
     * Will substitute numeric value for their labels for `Single` and `Multiple`
     * data types. In addition for `Multiple` data types the column header is
     * extended by the choice text. 
     * 
     * @see \berthott\SX\Exports\SxLabeledExport::headings()
     */
    public function getLabeledResource(Model $resource, bool $excludeFromExport = false): array
    {
        $ret = [];
        $questions = DB::table($resource->questionsTableName())->get()->keyBy('variableName');
        foreach ($resource->getAttributes() as $variableName => $value) {
            if ($excludeFromExport && in_array($variableName, config('sx.excludeFromExport'))) {
                continue;
            }
            $header = $variableName;
            if (!$questions->has($variableName)) {
                $ret[$header] = $value;
                continue;
            }
            switch ($questions[$variableName]->subType) {
                case 'Multiple':
                    $header = $variableName.' - '.$questions[$variableName]->choiceText;
                    // no break
                case 'Single':
                    $labelEntry = DB::table($resource->labelsTableName())
                        ->where('variableName', $variableName)
                        ->where('value', $value)
                        ->first();
                    $ret[$header] = $labelEntry ? $labelEntry->label : $value;
                    break;
                default:
                    $ret[$header] = $value;
            }
        }
        return $ret;
    }

    /**
     * Get a sorted list of columns for table.
     */
    public function getSortedColumns(string $tableName): array
    {
        try {
            return collect(
                DB::select(
                    (new MySqlGrammar)->compileColumnListing().' order by ordinal_position',
                    [DB::getDatabaseName(), $tableName]
                )
            )->pluck('column_name')->toArray();
        } catch (Exception $e) {
            return Schema::getColumnListing($tableName);
        }
    }

    /**
     * Log the execution time and return the result.
     */
    public function logExecutionTimeAndGetResult(string $label, Closure $cb): mixed
    {
        [$ret, $executionTime] = $this->getExecutionTimeAndResult($label, $cb);
        return $ret;
    }

    /**
     * Log the execution time and return the execution time.
     */
    public function logAndGetExecutionTime(string $label, Closure $cb): mixed
    {
        [$ret, $executionTime] = $this->getExecutionTimeAndResult($label, $cb);
        return $executionTime;
    }


    /**
     * Get the execution time and the result.
     */
    public function getExecutionTimeAndResult(string $label, Closure $cb): array
    {
        $startTime = microtime(true);
        $ret = $cb();
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        if (app()->runningInConsole()) {
            fwrite(STDERR, print_r("$label => $executionTime\r\n", TRUE));
        }
        SxLog::log("$label => $executionTime");
        return [$ret, $executionTime];
    }
}
