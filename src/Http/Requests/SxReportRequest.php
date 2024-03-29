<?php

namespace berthott\SX\Http\Requests;

use Illuminate\Support\Collection;
use Spatie\QueryBuilder\QueryBuilderRequest;

/**
 * QueryBuilderRequest extension to add custom aggregated query parameter.
 * 
 * @link https://spatie.be/docs/laravel-query-builder/v5/introduction
 * @api
 */
class SxReportRequest extends QueryBuilderRequest
{
    /**
     * The fields to be aggregated.
     * 
     * The aggregated query parameter is an array of fields to be aggregated.
     * e.g.
     * ```
     * 'trainer' => [
     *   'trainer_1',
     *   'trainer_2',
     *   'trainer_3',
     * ];
     * ```
     * 
     * @api
     */
    public function aggregate(): Collection
    { 
        $aggregate = collect($this->getRequestData('aggregate'));

        $aggregate = $aggregate->map(function($a) {
           $ret = is_string($a) ? explode(static::getIncludesArrayValueDelimiter(), $a) : $a;
           return $ret;
        });

        return $aggregate->filter();
    }

    /**
     * The fields to be included.
     * 
     * Overrides {@see \Spatie\QueryBuilder\QueryBuilderRequest::fields()} to
     * include fields that are included via the aggregated query parameter.
     */
    public function fields(): Collection
    {
        $allFields = parent::fields();
        $aggregate = $this->aggregate();
        $allFields = $allFields->map(function ($fields) use ($aggregate) {
            foreach ($fields as $field) {
                array_push($fields, ...($aggregate->has($field) ? $aggregate[$field] : []));
            }
            return $fields;
        });
        return $allFields;
    }
}
