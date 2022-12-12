<?php

namespace berthott\SX\Models\Traits;

use berthott\InternalRequest\Facades\InternalRequest;
use berthott\SX\Models\Respondent;
//use Illuminate\Support\Str;

trait SxDistributable
{
    /**
     * The single name of the model.
     */
    /* public static function singleName(): string
    {
        return Str::snake(class_basename(get_called_class()));
    } */

    /**
     * The entity table name of the model.
     */
    /* public static function entityTableName(): string
    {
        return Str::snake(Str::pluralStudly(class_basename(get_called_class())));
    } */

    /**
     * The class of the sxable to collect.
     */
    public static function sxable(): string
    {
        return '';
    }

    /**
     * An array of background variables to push to sx
     */
    public static function sxBackgroundVariables(self $distributable): array
    {
        return [];
    }

    public function collect(): Respondent
    {
        return InternalRequest::post(route(self::sxable()::entityTableName().'.create_respondent'), [
            'form_params' => array_merge(
                ['email' => 'monitoring@syspons.com'],
                self::sxBackgroundVariables($this),
            )
        ])->original;
    }
}
