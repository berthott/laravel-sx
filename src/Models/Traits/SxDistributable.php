<?php

namespace berthott\SX\Models\Traits;

use berthott\InternalRequest\Facades\InternalRequest;
use berthott\SX\Models\Respondent;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
    public static function sxBackgroundVariables($distributable): array
    {
        return [];
    }

    /**
     * An array of variables to be used inside a custom qr code pdf
     */
    public function sxPdfData(): array
    {
        return [];
    }

    /**
     * Return the data for the sx survey.
     */
    public function sxData(array $query): array
    {
        return [];
    }

    public function collect(): Respondent
    {
        $response = InternalRequest::skipMiddleware()->post(route(static::sxable()::entityTableName().'.create_respondent'), [
            'form_params' => array_merge(
                ['email' => 'monitoring@syspons.com'],
                static::sxBackgroundVariables($this),
            )
            ]);
        if ($response->exception) {
            throw $response->exception;
        }
        return $response->original;
    }

    public function qrCode(): string
    {
        $route = route(static::entityTableName().'.sxcollect', [ static::singleName() => $this->id ]);
        $encoded = base64_encode(QrCode::errorCorrection('H')->format('png')->size(250)->generate($route));
        return "data:image/png;base64,$encoded";
    }
}
