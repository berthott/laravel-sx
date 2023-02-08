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
         $aggregate = $this->getRequestData('aggregate');
 
         if (is_string($aggregate)) {
             $aggregate = explode(static::getIncludesArrayValueDelimiter(), $aggregate);
         }
 
         return collect($aggregate)->filter();
     }
}
