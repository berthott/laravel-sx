<?php

namespace berthott\SX\Models\Traits;

use berthott\SX\Models\SxMode;
use berthott\SX\Services\SxControllerService;
use Closure;
use Illuminate\Database\Schema\Blueprint;
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
     * The single name of the model.
     */
    private static $_sxController;


    /**
     * The single name of the model.
     */
    public static function controller(): SxControllerService
    {
        return self::$_sxController ?: self::$_sxController = new SxControllerService(self::surveyId());
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
        $entityStructure = self::controller()->getEntityStructure();
        self::initTable(self::entityTableName(), function (Blueprint $table) use ($entityStructure) {
            $table->bigIncrements('id');
            foreach ($entityStructure as $column) {
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
    }

    /**
     * Initialize the labels table.
     */
    public static function initLabelsTable(bool $force = false): void
    {
        self::initTable(self::labelsTableName(), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('questionName');
            $table->string('variableName');
            $table->smallInteger('choiceValue');
            $table->string('choiceText');
            $table->timestamps();
        }, $force);
    }

    /**
     * Initialize the questions table.
     */
    public static function initQuestionsTable(bool $force = false): void
    {
        self::initTable(self::questionsTableName(), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('questionName');
            $table->string('questionText');
            $table->timestamps();
        }, $force);
    }
}
