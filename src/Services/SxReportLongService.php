<?php

namespace berthott\SX\Services;

use berthott\SX\Http\Requests\SxReportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SxReportLongService
{
    private array $columns;
    private Collection $questions;
    private Collection $labels;

    /**
     * Build a report for the given class.
     */
    public function get(string $class): array
    {
        $this->columns = $this->fromOptions($class, 'filter') ?: $class::questionNames();
        $this->questions = $class::questions();
        $this->labels = $class::labels();
        return $this->report($class, $this->getData($class));
    }

    /**
     * Build a query builder query.
     */
    private function getData(string $class): Collection
    {    
        $request = SxReportRequest::fromRequest(app(Request::class));
        $respondents = $this->filterRespondents($class, $request);
        $data = $this->filterFields($class, $request, $respondents);
        return $this->aggregateFields($request, $data);
    }

    /**
     * Build a query to filter for the respondents requested.
     */
    private function filterRespondents(string $class, SxReportRequest $request): Collection
    {    
        return DB::table($class::longTableName())->where(function ($query) use ($class, $request) {            
            foreach($request->filters() as $property => $value) {
                $questions = $class::questions()->where('questionName', $property);
                $type = $questions->first()['subType'];
                $column = '';
                switch($type) {
                    case 'Single':
                    case 'Multiple':
                        $column = 'value_single_multiple';
                        break;
                    case 'Double':
                        $column = 'value_double';
                        break;
                    case 'String':
                        $column = 'value_string';
                        break;
                    case 'Date':
                        $column = 'value_datetime';
                        break;
                }
                $query = $query->orwhere(function($query) use ($column, $property, $value, $type, $questions) {
                    
                    if ($type === 'Multiple') {
                        if (!is_array($value)) {
                            $value = [$value];
                        }
                        $query = $query->whereIn('variableName', $questions->where('questionName', $property)->pluck('variableName'));
                        $query = $query->where(function($query) use ($value, $questions, $column) {
                            foreach ($value as $v) {
                                foreach ($questions as $question) {
                                    if ($question['choiceValue'] === $v) {
                                        $query = $query->orwhere(function($query) use ($question, $column) {
                                            return $query->where('variableName', $question['variableName'])->where($column, 1);
                                        });
                                    }
                                }
                            }
                        });
                        return $query;
                    }
                    $query = $query->where('variableName', $property);
                    return is_array($value) 
                            ? $query->whereIn($column, $value) 
                            : $query->where($column, $value);
                });
            }
        })->get()->unique('respondent_id')->pluck('respondent_id');
    }

    /**
     * Build a query to filter for the fields requested.
     */
    private function filterFields(string $class, SxReportRequest $request, Collection $respondents): Collection
    {    
        return DB::table($class::longTableName())->where(function ($query) use ($respondents, $request, $class) {
            $fields = $request->fields();
            if ($fields->count()) {
                $query = $query->whereIn('variableName', $fields[$class::entityTableName()]);
            }
            return $query->whereIn('respondent_id', $respondents);
        })->get();
    }

    /**
     * Build a query to filter for the fields requested.
     */
    private function aggregateFields(SxReportRequest $request, Collection $data): Collection
    {    
        $aggregate = $request->aggregate();
        if (!$aggregate->count()) {
            return $data;
        }
        
        foreach($aggregate as $replace => $search) {
            // collumns 
            array_push($this->columns, $replace);
            $this->columns = array_diff($this->columns, $search);
            // questions
            $newQuestion = $this->questions->filter(fn($question) => in_array($question['variableName'], $search))->first();
            $newQuestion['variableName'] = $replace;
            $newQuestion['questionName'] = $replace;
            $this->questions = $this->questions->filter(fn($question) => !in_array($question['variableName'], $search));
            $this->questions->push($newQuestion);
            // labels
            $this->labels = $this->labels->map(function($label) use ($replace, $search) {
                if (in_array($label['variableName'], $search)) {
                    $label['variableName'] = $replace;
                }
                return $label;
            });
        }
        return $data->map(function($entry) use ($aggregate) {
            foreach($aggregate as $replace => $search) {
                if (in_array($entry->variableName, $search)) {
                    $entry->variableName = $replace;
                }
            }
            return $entry;
        });
    }

    /**
     * Gather report data.
     */
    private function report(string $class, Collection $data): array
    {
        $ret = [];
        if ($data->count()) {
            foreach($this->columns as $column) {
                $filteredQuestions = $this->questions->where('questionName', $column);
                $filteredData = $data->filter(function($entry) use ($filteredQuestions) {
                    return $filteredQuestions->contains(function($question) use ($entry) {
                        return $question['variableName'] === $entry->variableName;
                    });
                });
                if ($filteredData->isEmpty()) {
                    continue;
                }
                $question = $filteredQuestions->first();
                switch ($question['subType']) {
                    case 'Single':
                        $ret[$column] = $this->reportSingle($class, $filteredData, $question);
                        break;
                    case 'Multiple':
                        $ret[$column] = $this->reportMultiple($class, $filteredData, $question);
                        break;
                    case 'Double':
                        $ret[$column] = $this->reportDouble($class, $filteredData, $question);
                        break;
                    case 'Date':
                        $ret[$column] = $this->reportDate($class, $filteredData, $question);
                        break;
                    case 'String':
                    default:
                        $ret[$column] = $this->reportString($class, $filteredData, $question);
                        break;
                }
            }
        }
        return $ret;
    }

    private function reportSingle(string $class, Collection $data, array $question): array
    {
        $possibleAnswers = $this->labels->where('variableName', $question['variableName'])->unique();
        $answers = $data->where('variableName', $question['variableName'])->pluck('value_single_multiple')->values();
        $validAnswers = $answers->filter(fn($answer) => $answer > 0);
        $validAnswersPercent = $possibleAnswers->pluck('value')->mapWithKeys(function($value) use ($validAnswers) {
            $count = $validAnswers->filter(fn($v) => $v == $value)->count();
            $percentage = $count ? round($count * 100 / $validAnswers->count(), 2) : 0;
            return [$value => $percentage];
        });
        return $this->buildReport($answers, $validAnswers, $question, [
            'labels' => $possibleAnswers->mapWithKeys(fn($a) => [$a['value'] => $a['label']])->toArray(),
            'answersPercent' => $validAnswersPercent->toArray(),
            'average' => round($validAnswers->average(), 2),
        ]);
    }

    private function reportMultiple(string $class, Collection $data, array $question): array
    {
        $possibleAnswers = $this->questions->where('questionName', $question['questionName']);
        $possibleValues = $possibleAnswers->mapWithKeys(fn($a) => [$a['choiceValue'] => $a['choiceText']]);
        $answers = $data->groupBy('respondent_id')->map(function($group) use ($possibleAnswers) {
            return $group
                ->filter(fn($entry) => $entry->value_single_multiple === 1)
                ->map(fn($entry) => $possibleAnswers->firstWhere('variableName', $entry->variableName)['choiceValue'])
                ->sort()->values()->toArray();
        });
        $num = $answers->count();
        $validAnswers = $answers->filter(fn($answer) => count($answer) > 0);
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
        $answers = $data->where('variableName', $question['variableName'])->pluck('value_double')->values();
        $validAnswers = $answers->filter(fn($answer) => $answer != null);
        return $this->buildReport($answers, $validAnswers, $question, [
            'average' => $validAnswers->average(),
        ]);
    }

    private function reportString(string $class, Collection $data, array $question): array
    {
        $answers = $data->where('variableName', $question['variableName'])->pluck('value_string')->values();
        $validAnswers = $answers->filter(fn($answer) => $answer != null);
        return $this->buildReport($answers, $validAnswers, $question);
    }

    private function reportDate(string $class, Collection $data, array $question): array
    {
        $answers = $data->where('variableName', $question['variableName'])->pluck('value_datetime')->values();
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
