<?php

namespace berthott\SX\Models\Traits;

use berthott\SX\Models\SxMode;
use berthott\SX\Services\SxSurveyService;
use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
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
     * The fields that should be processed.
     */
    public static function unique(): array
    {
        return config('sx.defaultUnique');
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
     * The fields to be processed.
     */
    private static $_fields;

    /**
     * The controller service.
     */
    private static $_sxController;

    /**
     * The survey controller
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
            $t = null;
            $table->bigIncrements('id');
            foreach ($entityStructure as $column) {
                if (!in_array($column['variableName'], self::fields())) {
                    continue;
                }
                switch ($column['subType']) {
                    case 'Single':
                    case 'Multiple':
                        $t = $table->integer($column['variableName']);
                        break;
                    case 'Double':
                        $t = $table->double($column['variableName']);
                        break;
                    case 'String':
                        $t = $table->string($column['variableName']);
                        break;
                    case 'Date':
                        $t = $table->dateTime($column['variableName']);
                        break;
                }
                if (in_array($column['variableName'], self::unique())) {
                    $t->unique();
                }
            }
            $table->timestamps();
        }, $force);

        if (DB::table($table)->get()->isEmpty()) {
            DB::table($table)->insert(self::entities()->all());
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
            $table->timestamp('created_at');
        }, $force);

        if (DB::table($table)->get()->isEmpty()) {
            DB::table($table)->insert(
                self::addTimestamp(
                    self::controller()->getLabels(),
                    'created_at'
                )->all()
            );
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
            $table->timestamp('created_at');
        }, $force);

        if (DB::table($table)->get()->isEmpty()) {
            DB::table($table)->insert(
                self::addTimestamp(
                    self::controller()->getQuestions(),
                    'created_at'
                )->all()
            );
        }
    }

    /**
     * The fields to be processed.
     */
    private static function fields(): array
    {
        return self::$_fields ?: self::$_fields =
            !empty(self::include())
                ? array_intersect(self::controller()->getEntityStructure()->pluck('variableName')->all(), self::include())
                : (!empty(self::exclude())
                    ? array_diff(self::controller()->getEntityStructure()->pluck('variableName')->all(), self::exclude())
                    : self::controller()->getEntityStructure()->pluck('variableName')->all());
    }

    /**
     * The entities mapped to the fields.
     */
    private static function entities(array $query = []): Collection
    {
        return self::controller()->getEntities($query)->map(function ($entity) {
            return array_intersect_key($entity, array_fill_keys(self::fields(), ''));
        });
    }

    private static function addTimestamp(Collection $collection, ...$args): Collection
    {
        return $collection->map(function ($item) use ($args) {
            foreach ($args as $arg) {
                $item[$arg] = now();
            };
            return $item;
        });
    }
}
