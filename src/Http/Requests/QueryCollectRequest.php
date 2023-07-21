<?php

namespace berthott\SX\Http\Requests;

use Facades\berthott\SX\Services\SxDistributableService;
use Illuminate\Foundation\Http\FormRequest;

class QueryCollectRequest extends FormRequest
{
    private string $target;

    public function __construct()
    {
        $this->target = SxDistributableService::getTarget();
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * Validates all in {@see \berthott\SX\Models\Traits\SxDistributable::distributableQueryCollectParams()}
     * defined parameters. If given the custom validation is used, if none is given `nullable` will
     * be applied.
     * 
     * @api
     */
    public function rules(): array
    {
        $ret = [];
        foreach ($this->target::distributableQueryCollectParams() as $param => $validation) {
            if (is_int($param)) {
                $ret[$validation] = 'nullable';
            } else {
                $ret[$param] = $validation;
            }
        }
        return $ret;
    }
}
