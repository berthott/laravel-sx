<?php

namespace berthott\SX\Services;

use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SxableService
{
    /**
     * Collection with all sxable classes.
     */
    private Collection $sxables;

    /**
     * The Constructor.
     */
    public function __construct()
    {
        $this->initSxableClasses();
        $this->initTables();
    }

    /**
     * Get the sxable classes collection.
     */
    public function getSxableClasses(): Collection
    {
        return $this->sxables;
    }

    /**
     * Initialize the sxable classes collection.
     */
    private function initSxableClasses(): void
    {
        $sxables = [];
        $namespaces = config('sx.namespace');
        foreach (is_array($namespaces) ? $namespaces : [$namespaces] as $namespace) {
            foreach (ClassFinder::getClassesInNamespace($namespace) as $class) {
                foreach (class_uses_recursive($class) as $trait) {
                    if ('berthott\SX\Models\Traits\Sxable' == $trait) {
                        array_push($sxables, $class);
                    }
                }
            }
        }
        $this->sxables = collect($sxables);
    }

    /**
     * Get the target model.
     */
    public function getTarget(): string
    {
        if (!request()->segments() || !$this->sxables) {
            return '';
        }
        $model = Str::studly(Str::singular(request()->segment(count(explode('/', config('sx.prefix'))) + 1)));

        return $this->sxables->first(function ($sxable) use ($model) {
            return Str::contains($sxable, $model);
        });
    }

    /**
     * Initialize the sxable tables.
     */
    private function initTables(): void
    {
        if (!$this->sxables) {
            return;
        }

        foreach ($this->sxables as $sxable) {
            $single = Str::lower(class_basename($sxable));
            $entityTable = Str::plural($single);
            $valuesTable = $single.'_values';
            $questionsTable = $single.'_questions';

            $sx = new SxControllerService($sxable::surveyId());

            if (!Schema::hasTable($entityTable)) {
                Schema::create($entityTable, function (Blueprint $table) use ($sx) {
                    $table->bigIncrements('id');
                    foreach ($sx->getEntityStructure() as $column) {
                        switch ($column['subType']) {
                            case 'Single':
                            case 'Multiple':
                                $table->integer($column['variableName']);
                                break;
                            case 'Double':
                                $table->double($column['variableName']);
                                break;
                            case 'String':
                                $table->string($column['variableName']);
                                break;
                            case 'Date':
                                $table->dateTime($column['variableName']);
                                break;
                        }
                    }
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable($valuesTable)) {
                Schema::create($valuesTable, function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->string('questionName');
                    $table->string('variableName');
                    $table->smallInteger('choiceValue');
                    $table->string('choiceText');
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable($questionsTable)) {
                Schema::create($questionsTable, function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->string('questionName');
                    $table->string('questionText');
                    $table->timestamps();
                });
            }
        }
    }
}
