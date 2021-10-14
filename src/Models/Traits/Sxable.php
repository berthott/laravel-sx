<?php

namespace berthott\SX\Models\Traits;

use berthott\SX\Models\SxMode;
use Illuminate\Support\Str;

trait Sxable
{
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
    public static function entityTable(): string
    {
        return Str::plural(self::singleName());
    }

    /**
     * The labels table name of the model.
     */
    public static function labelsTable(): string
    {
        return self::singleName().'_labels';
    }

    /**
     * The questions table name of the model.
     */
    public static function questionsTable(): string
    {
        return self::singleName().'_questions';
    }

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
}
