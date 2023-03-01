<?php

namespace berthott\SX\Http\Requests;

use berthott\SX\Facades\Sxable;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\QueryBuilderRequest;

class SxReportRequest extends QueryBuilderRequest
{
    /**
    * Get the validation rules that apply to the request.
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
