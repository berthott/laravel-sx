<?php

namespace berthott\SX\Http\Requests;

use Illuminate\Support\Collection;
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
