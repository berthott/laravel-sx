<?php

namespace berthott\SX\Services;

use berthott\SX\Http\Requests\SxReportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

const REPORT_DECIMALS = 1;

/**
 * Service for generating report data.
 * 
 * @see \berthott\SX\Http\Requests\SxReportRequest
 * @link https://docs.syspons-dev.com/ngs-core/modules/_syspons_ngs_sx_report.html @syspons/ngs-sx-report
 */
class SxReportService
{
    private array $columns;
    private Collection $questions;
    private Collection $aggregatedQuestions;
    private Collection $labels;
    private Collection $requestedRespondents;
    private SxReportRequest $request;

    /**
     * Build a report for the given class.
     */
    public function get(string $class): array
    {
        $this->request = SxReportRequest::fromRequest(app(Request::class));
        $this->questions = $this->aggregatedQuestions = $class::questions($this->request->lang);
        $this->columns = $this->fromOptions($class, 'filter') ?: $this->getColumns($class);
        $this->labels = $class::labels($this->request->lang);
        $this->requestedRespondents = $this->filterRespondents($class, $this->request);
        $this->setupForAggregatedFields($this->request);
        return $this->report($class);
    }

    /**
     * Get the columns for the report.
     */
    private function getColumns(string $class): array
    {
        $questions = $this->questions;
        $fields = $this->request->fields();
            if ($fields->count()) {
                $questions = $questions->whereIn('variableName', $fields[$class::entityTableName()]);
            }
        return $questions->pluck('questionName')->unique()->values()->toArray();
    }

    /**
     * Build a query to filter for the respondents requested.
     */
    private function filterRespondents(string $class, SxReportRequest $request): Collection
    {    
        return DB::table($class::entityTableName())->where(function ($query) use ($request) {            
            foreach($request->filters() as $property => $value) {
                $questions = $this->questions->where('questionName', $property);
                $type = $questions->first()['subType'];
                $query = $query->orwhere(function($query) use ($property, $value, $type, $questions) {
                    if ($type === 'Multiple') {
                        if (!is_array($value)) {
                            $value = [$value];
                        }
                        $query = $query->where(function($query) use ($value, $questions) {
                            foreach ($value as $v) {
                                $question = $questions->first(fn($q) => $q['choiceValue'] === (int) $v);
                                if ($question) {
                                    $query = $query->orwhere(function($query) use ($question) {
                                        return $query->where($question['variableName'], 1);
                                    });
                                }
                            }
                        });
                        return $query;
                    }
                    return is_array($value) 
                            ? $query->whereIn($property, $value) 
                            : $query->where($property, $value);
                });
            }
        })->get();
    }

    /**
     * Build aggregated columns, questions and labels.
     */
    private function setupForAggregatedFields(SxReportRequest $request)
    {        
        foreach($request->aggregate() as $replace => $search) {
            // columns 
            array_push($this->columns, $replace);
            $this->columns = array_diff($this->columns, $search);
            // questions
            $newQuestion = $this->questions->filter(fn($question) => in_array($question['variableName'], $search))->first();
            $newQuestion['variableName'] = $replace;
            $newQuestion['questionName'] = $replace;
            $this->aggregatedQuestions = $this->aggregatedQuestions->filter(fn($question) => !in_array($question['variableName'], $search));
            $this->aggregatedQuestions->push($newQuestion);
            // labels
            $this->labels = $this->labels->map(function($label) use ($replace, $search) {
                if (in_array($label['variableName'], $search)) {
                    $label['variableName'] = $replace;
                }
                return $label;
            });
        }
    }

    /**
     * Build the report from the gathered data.
     * 
     * This is done for each data type individually.
     */
    private function report(string $class): array
    {
        $ret = [];
        foreach($this->columns as $column) {
            $aggregated = $this->request->aggregate()->get($column);
            $filteredQuestions = $aggregated ? $this->questions->whereIn('questionName', $aggregated) : $this->questions->where('questionName', $column);

            $question = $this->aggregatedQuestions->where('questionName', $column)->first();
            $method = 'report'.$question['subType'];
            $d = $this->$method($class, $question);
            if ($d['numValid']) {
                $ret[$column] = $d;
            }
        }
        return $ret;
    }

    /**
     * Get the data for a single question.
     * Handling of aggregated fields.
     */
    private function getSingleData(string $question): Collection
    {
        $aggregate = $this->request->aggregate();
        if (!$aggregate->count() || !$aggregate->has($question)) {
            return $this->requestedRespondents->pluck($question)->values();
        }

        $aggregated = collect();
        if ($aggregate->has($question)) {
            $this->requestedRespondents->each(function($respondent) use ($question, $aggregate, &$aggregated) {
                foreach($aggregate[$question] as $search) {
                    $aggregated->push($respondent->$search);
                }
            });
            
        }

        return $aggregated;
    }

    private function reportSingle(string $class, array $question): array
    {
        $possibleAnswers = $this->labels->where('variableName', $question['variableName'])->unique()->filter(fn($a) => $a['value'] > 0);
        $answers = $this->getSingleData($question['variableName']);
        $validAnswers = $answers->filter(fn($answer) => $answer > 0);
        $validAnswersCount = $possibleAnswers->pluck('value')->mapWithKeys(function($value) use ($validAnswers) {
            $count = $validAnswers->filter(fn($v) => $v == $value)->count();
            return [$value => $count];
        });
        $validAnswersPercent = $validAnswersCount->map(function($count) use ($validAnswers) {
            return $count ? round($count * 100 / $validAnswers->count(), REPORT_DECIMALS) : 0;
        });
        return $this->buildReport($answers, $validAnswers, $question, [
            'labels' => $possibleAnswers->mapWithKeys(fn($a) => [$a['value'] => $a['label']])->toArray(),
            'answersCount' => $validAnswersCount->toArray(),
            'answersPercent' => $validAnswersPercent->toArray(),
            'average' => round($validAnswers->average(), REPORT_DECIMALS),
        ]);
    }

    private function reportMultiple(string $class, array $question): array
    {
        $possibleAnswers = $this->questions->where('questionName', $question['questionName']);
        $possibleValues = $possibleAnswers->mapWithKeys(fn($a) => [$a['choiceValue'] => $a['choiceText']]);
        $answers = $this->requestedRespondents->map(function($respondent) use ($possibleAnswers) {
            return $possibleAnswers->reduce(function($r, $possibleAnswer) use ($respondent) {
                $variableName = $possibleAnswer['variableName'];
                if ($respondent->$variableName === 1) {
                    $r[] = $possibleAnswer['choiceValue'];
                }
                return $r;
            }, []);
        });
        $num = $answers->count();
        $validAnswers = $answers->filter(fn($answer) => count($answer) > 0);
        $validAnswersFlat = $validAnswers->flatten();
        $validAnswersCount = $possibleAnswers->pluck('choiceValue')->mapWithKeys(function($value) use ($validAnswersFlat, $num) {
            $count = $validAnswersFlat->filter(fn($v) => $v == $value)->count();
            return [$value => $count];
        });
        $validAnswersPercent = $validAnswersCount->map(function($count) use ($num) {
            return $num ? round($count * 100 / $num, REPORT_DECIMALS) : 0;
        });
        return $this->buildReport($answers, $validAnswers, $question, [
            'labels' => $possibleValues->toArray(),
            'answersCount' => $validAnswersCount->toArray(),
            'answersPercent' => $validAnswersPercent->toArray(),
        ]);
    }

    private function reportDouble(string $class, array $question): array
    {
        $answers = $this->requestedRespondents->pluck($question['variableName'])->values();
        $validAnswers = $answers->filter(fn($answer) => $answer != null);
        return $this->buildReport($answers, $validAnswers, $question, [
            'average' => $validAnswers->average(),
        ]);
    }

    private function reportString(string $class, array $question): array
    {
        $answers = $this->requestedRespondents->pluck($question['variableName'])->values();
        $validAnswers = $answers->filter(fn($answer) => $answer != null);
        return $this->buildReport($answers, $validAnswers, $question);
    }

    private function reportDate(string $class, array $question): array
    {
        $answers = $this->requestedRespondents->pluck($question['variableName'])->values();
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
