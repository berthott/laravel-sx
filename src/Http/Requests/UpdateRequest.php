<?php

namespace berthott\SX\Http\Requests;

use Facades\berthott\SX\Services\SxableService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    private string $target;

    public function __construct()
    {
        $this->target = SxableService::getTarget();
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * Unique fields will be validated unique.
     * All other fields will be validated nullable.
     * 
     * @api
     */
    public function rules(): array
    {
        $ret = [
          'form_params.email' => 'nullable|email',
        ];
        foreach ($this->target::uniqueFields() as $field) {
            $ret['form_params.'.$field] = 'nullable|unique:'.$this->target::entityTableName();
        }
        $ret['form_params.*'] = 'nullable';
        return $ret;
    }
}
