<?php

namespace berthott\SX\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Helpers
{
    /**
     * Get the values for the values table.
     */
    public function pluckFromCollection(Collection $collection, string ...$args): Collection
    {
        return $collection->map(function ($item) use ($args) {
            return array_intersect_key($item, array_fill_keys($args, ''));
        });
    }

    /**
     * Get a labeled resource.
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
}
