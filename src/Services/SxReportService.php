<?php

namespace berthott\SX\Services;

use berthott\SX\Http\Requests\Filters\SxFilter;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SxReportService
{
    /**
     * Build a report for the given class.
     */
    public function get(string $class): Collection
    {
        return $this->getQuery($class);
    }

    /**
     * Build a query builder query.
     */
    private function getQuery(string $class): Collection
    {
        $filters = [];
        foreach ($this->fromOptions($class, 'filter') ?: $class::questionNames() as $filter) {
            $filters[] = AllowedFilter::custom($filter, new SxFilter($class));
        }
        return QueryBuilder::for($class)
            ->allowedFilters($filters)
            ->allowedFields($this->fromOptions($class, 'fields') ?: $class::fields())
            ->get();
    }

    /**
     * Get the user defined array from the options.
     */
    private function fromOptions(string $class, string $attribute): array | null
    {
        $options = $class::reportQueryOptions();
        if (array_key_exists($attribute, $options)) {
            return $options[$attribute];
        }

        return null;
    }
}
