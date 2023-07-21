<?php

namespace berthott\SX\Models\Traits;

use berthott\InternalRequest\Facades\InternalRequest;
use berthott\SX\Models\Respondent;
use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

/**
 * Trait to add the SxDistributable functionality.
 * 
 * An SxDistributable can be any class that is logically connected 
 * to an {@see \berthott\SX\Models\Traits\Sxable}.
 */
trait SxDistributable
{
    /**
     * The class of the sxable to collect.
     * 
     * **required**
     * 
     * @api
     */
    public static function sxable(): string
    {
        return '';
    }

    /**
     * An array of background variables to push to SX.
     * 
     * **optional**
     * 
     * Defaults to `[]`.
     * 
     * @api
     */
    public static function sxBackgroundVariables(Model $distributable): array
    {
        return [];
    }

    /**
     * An array of variables to be used inside a custom qr code PDF.
     * 
     * **optional**
     * 
     * Defaults to `[]`.
     * 
     * @api
     */
    public function sxPdfData(): array
    {
        return [];
    }

    /**
     * Data to be made available for the SX survey to query.
     * 
     * **optional**
     * 
     * Defaults to `[]`.
     * 
     * @api
     */
    public function sxData(array $query): array
    {
        return [];
    }

    /**
     * The single name of the model.
     */
    public static function distributableSingleName(): string
    {
        return Str::snake(class_basename(get_called_class()));
    }

    /**
     * The entity table name of the model.
     */
    public static function distributableEntityTableName(): string
    {
        return Str::snake(Str::pluralStudly(class_basename(get_called_class())));
    }

    /**
     * Possible params for collecting a distributable.
     * 
     * The array can contain strings, in which case it's validation will be
     * nullable, or associative query => validation_rule values.
     * 
     * ```php
     * [
     *   'id',
     *   'special_id' => 'required_string',
     * ]
     * ```
     * 
     * **optional**
     * 
     * Defaults to `[]`.
     */
    public static function distributableQueryCollectParams(): array
    {
        return [];
    }

    /**
     * Create a new SX respondent on a connected Sxable.
     * 
     * @throws \Exception
     */
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

    /**
     * The collect URL.
     */
    public function collectUrl(): string
    {
        return route(static::distributableEntityTableName().'.sxcollect', [ static::distributableSingleName() => $this->id ]);
    }

    /**
     * A base64 representation of a QR Code PNG of the collect URL.
     */
    public function qrCode(): string
    {
        $encoded = base64_encode(QrCode::errorCorrection('H')->format('png')->size(250)->generate($this->collectUrl()));
        return "data:image/png;base64,$encoded";
    }
}
