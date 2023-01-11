<?php

namespace berthott\SX\Services;

use berthott\SX\Http\Requests\Filters\SxFilter;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SxReportService
{
    private array $columns;

    /**
     * Build a report for the given class.
     */
    public function get(string $class): array
    {
        $this->columns = $this->fromOptions($class, 'filter') ?: $class::questionNames();
        return $this->report($class, $this->getData($class));
    }

    /**
     * Build a query builder query.
     */
    private function getData(string $class): Collection
    {
        $filters = [];
        foreach ($this->columns as $filter) {
            $filters[] = AllowedFilter::custom($filter, new SxFilter($class));
        }
        return QueryBuilder::for($class)
            ->allowedFilters($filters)
            ->allowedFields($this->fromOptions($class, 'fields') ?: $class::fields())
            ->get();
    }

    /**
     * Gather report data.
     */
    private function report(string $class, Collection $data): array
    {
        $ret = [];
        if ($data->count()) {
            $questions = $class::questions();
            foreach($this->columns as $column) {
                $question = $questions->where('questionName', $column)->first();
                if (!array_key_exists($question['variableName'], $data->first()->getAttributes())) {
                    continue;
                }
                switch ($question['subType']) {
                    case 'Single':
                        $ret[$column] = $this->reportSingle($class, $data, $question);
                        break;
                    case 'Multiple':
                        $ret[$column] = $this->reportMultiple($class, $data, $question);
                        break;
                    case 'Double':
                        $ret[$column] = $this->reportDouble($class, $data, $question);
                        break;
                    case 'Date':
                    case 'String':
                    default:
                        $ret[$column] = $this->reportString($class, $data, $question);
                        break;
                }
            }
        }
        return $ret;
    }

    private function reportSingle(string $class, Collection $data, array $question): array
    {
        $possibleAnswers = $class::labels()->where('variableName', $question['variableName']);
        $answers = $data->pluck($question['variableName']);
        $validAnswers = $answers->filter(fn($answer) => $answer > 0);
        $validAnswersPercent = $possibleAnswers->pluck('value')->mapWithKeys(function($value) use ($validAnswers) {
            $count = $validAnswers->filter(fn($v) => $v == $value)->count();
            $percentage = $count ? round($count * 100 / $validAnswers->count(), 2) : 0;
            return [$value => $percentage];
        });
        return $this->buildReport($answers, $validAnswers, $question, [
            'labels' => $possibleAnswers->mapWithKeys(fn($a) => [$a['value'] => $a['label']])->toArray(),
            'answersPercent' => $validAnswersPercent->toArray(),
            'average' => $validAnswers->average(),
        ]);
    }

    private function reportMultiple(string $class, Collection $data, array $question): array
    {
        $possibleAnswers = $class::questions()->where('questionName', $question['questionName']);
        $possibleValues = $possibleAnswers->mapWithKeys(fn($a) => [$a['choiceValue'] => $a['choiceText']]);
        $answers = $data->map(function($entry) use ($possibleAnswers) {
            return $possibleAnswers->reduce(function($r, $answer) use ($entry) {
                $name = $answer['variableName'];
                $value = $entry->$name;
                if ($value === 1) {
                    $r[] = +$answer['choiceValue'];
                }
                return $r;
            }, []);
        });
        $validAnswers = $answers->filter(fn($answer) => count($answer) > 0);
        $num = $answers->count();
        $validAnswersFlat = $validAnswers->flatten();
        $validAnswersPercent = $possibleAnswers->pluck('choiceValue')->mapWithKeys(function($value) use ($validAnswersFlat, $num) {
            $count = $validAnswersFlat->filter(fn($v) => $v == $value)->count();
            $percentage = $num ? round($count * 100 / $num, 2) : 0;
            return [$value => $percentage];
        });
        return $this->buildReport($answers, $validAnswers, $question, [
            'labels' => $possibleValues->toArray(),
            'answersPercent' => $validAnswersPercent->toArray(),
        ]);
    }

    private function reportDouble(string $class, Collection $data, array $question): array
    {
        $answers = $data->pluck($question['variableName']);
        $validAnswers = $answers->filter(fn($answer) => $answer != null);
        return $this->buildReport($answers, $validAnswers, $question, [
            'average' => $validAnswers->average(),
        ]);
    }

    private function reportString(string $class, Collection $data, array $question): array
    {
        $answers = $data->pluck($question['variableName']);
        $validAnswers = $answers->filter(fn($answer) => $answer != null);
        return $this->buildReport($answers, $validAnswers, $question);
    }

    private function buildReport(Collection $answers, Collection $validAnswers, array $question, array $additional = []): array
    {
        $num = $answers->count();
        $numValid = $validAnswers->count();
        return array_merge(
            [
                'type' => $question['subType'],
                'question' => $question['questionText'],
                'answers' => $validAnswers->values()->toArray(),
                'num' => $num,
                'numValid' => $numValid,
                'numInvalid' => $num - $numValid,
            ],
            $additional,
        );
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
