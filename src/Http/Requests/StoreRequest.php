<?php

namespace berthott\SX\Http\Requests;

use Facades\berthott\SX\Services\SxableService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Simple FormRequest Implementation for validation.
 */
class StoreRequest extends FormRequest
{
    private string $target;

    public function __construct()
    {
        $this->target = SxableService::getTarget();
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * See the {@link https://documenter.getpostman.com/view/1760772/S1a33ni6#1a6ed025-3d04-4c15-948c-692f1c8b65ae SX API Documentation}
     * for more info's on the possible fields.
     * `email`is required.
     * Unique fields will be validated unique.
     * All other fields will be validated nullable.
     * 
     * @api
     */
    public function rules(): array
    {
        $ret = [
          'query.distributionTs' => 'nullable|date',
          'query.reminder1Ts' => 'nullable|date',
          'query.reminder2Ts' => 'nullable|date',
          'query.channel' => 'nullable|string|in:Email,SMS,Eboks,Eboksbusiness',
          'query.customKey' => 'nullable|string',
          'form_params.email' => 'required|email',
        ];
        foreach ($this->target::uniqueFields() as $field) {
            $ret['form_params.'.$field] = 'nullable|unique:'.$this->target::entityTableName();
        }
        $ret['form_params.*'] = 'nullable';
        return $ret;
    }
}
