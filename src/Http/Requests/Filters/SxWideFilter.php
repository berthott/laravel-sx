<?php 

namespace berthott\SX\Http\Requests\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class SxWideFilter implements Filter
{
    /**
     * Table to filter on.
     */
    private string $target;

    public function __construct(string $target)
    {
        $this->target = $target;
    }

    /**
     * Handle special case for Multiple question type
     *
     * @param Builder $query
     * @param any $value
     * @param string $property
     * @return Builder
     */
    public function __invoke(Builder $query, $value, string $property) : Builder
    {
        return $query->where(function ($query) use ($value, $property) {
            $questions = $this->target::questions()->where('questionName', $property);
            if ($questions->first() && $questions->first()['subType'] === 'Multiple') {
                if (!is_array($value)) {
                    $value = [$value];
                }
                foreach ($value as $v) {
                    foreach ($questions as $question) {
                        if ($question['choiceValue'] === $v) {
                            $query = $query->orwhere($question['variableName'], 1);
                        }
                    }
                }
                return $query;
            }
            return is_array($value) 
                ? $query->whereIn($property, $value) 
                : $query->where($property, $value);
        });
    }
}

