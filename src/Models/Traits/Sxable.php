<?php

namespace berthott\SX\Models\Traits;

use berthott\SX\Models\SxMode;
use berthott\SX\Services\SxSurveyService;
use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait Sxable
{
    /**
     * The Survey Id that should be connected to this Model.
     */
    public static function surveyId(): string
    {
        return '';
    }

    /**
     * The format in which the data should be stored
     */
    public static function format(): string
    {
        return SxMode::Entity;
    }

    /**
     * The fields that should be excluded from being processed.
     * Will be ignored when include is set
     */
    public static function exclude(): array
    {
        return [];
    }

    /**
     * The fields that should be processed.
     */
    public static function include(): array
    {
        return [];
    }

    /**
     * Returns an array of route options.
     * See Route::apiResource documentation.
     */
    public static function routeOptions(): array
    {
        return [];
    }

    /**
     * The fileds to be processed.
     */
    private static $_fields;

    /**
     * The controller service.
     */
    private static $_sxController;

    /**
     * The single name of the model.
     */
    public static function controller(): SxSurveyService
    {
        return self::$_sxController ?: self::$_sxController = new SxSurveyService(self::surveyId());
    }

    /**
     * The single name of the model.
     */
    public static function singleName(): string
    {
        return Str::lower(class_basename(get_called_class()));
    }

    /**
     * The entity table name of the model.
     */
    public static function entityTableName(): string
    {
        return Str::plural(self::singleName());
    }

    /**
     * The labels table name of the model.
     */
    public static function labelsTableName(): string
    {
        return self::singleName().'_labels';
    }

    /**
     * The questions table name of the model.
     */
    public static function questionsTableName(): string
    {
        return self::singleName().'_questions';
    }

    /**
     * Initialize the sxable tables.
     */
    public static function initTables(bool $force = false): void
    {
        self::initEntityTable($force);
        self::initLabelsTable($force);
        self::initQuestionsTable($force);
    }

    /**
     * Initialize the a table with the given name and closure.
     */
    private static function initTable(string $name, Closure $callback, bool $force = false): void
    {
        if ($force) {
            Schema::dropIfExists($name);
        }

        if (!Schema::hasTable($name)) {
            Schema::create($name, $callback);
        }
    }

    /**
     * Initialize the entity table.
     */
    public static function initEntityTable(bool $force = false): void
    {
        $table = self::entityTableName();
        $entityStructure = self::controller()->getEntityStructure();
        self::initTable($table, function (Blueprint $table) use ($entityStructure) {
            $table->bigIncrements('id');
            foreach ($entityStructure as $column) {
                if (!in_array($column['variableName'], self::fields())) {
                    continue;
                }
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
        }, $force);

        if (DB::table($table)->get()->isEmpty()) {
            DB::table($table)->insert(self::entities());
        }
    }

    /**
     * Initialize the labels table.
     */
    public static function initLabelsTable(bool $force = false): void
    {
        $table = self::labelsTableName();
        self::initTable($table, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('variableName');
            $table->integer('value');
            $table->string('label');
            $table->timestamps();
        }, $force);

        if (DB::table($table)->get()->isEmpty()) {
            DB::table($table)->insert(self::controller()->getLabels()->all());
        }
    }

    /**
     * Initialize the questions table.
     */
    public static function initQuestionsTable(bool $force = false): void
    {
        $table = self::questionsTableName();
        self::initTable($table, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('questionName');
            $table->string('variableName');
            $table->string('questionText');
            $table->string('subType');
            $table->integer('choiceValue')->nullable();
            $table->string('choiceText')->nullable();
            $table->timestamps();
        }, $force);

        if (DB::table($table)->get()->isEmpty()) {
            DB::table($table)->insert(self::controller()->getQuestions()->all());
        }
    }

    private static function fields(): array
    {
        return self::$_fields ?: self::$_fields =
            !empty(self::include())
                ? array_intersect(self::controller()->getEntityStructure()->pluck('variableName')->all(), self::include())
                : (!empty(self::exclude())
                    ? array_diff(self::controller()->getEntityStructure()->pluck('variableName')->all(), self::exclude())
                    : self::controller()->getEntityStructure()->pluck('variableName')->all());
    }

    private static function entities(): array
    {
        return self::controller()->getEntities()->map(function ($entity) {
            return array_intersect_key($entity, array_fill_keys(self::fields(), ''));
        })->all();
    }
}
