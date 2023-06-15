<?php

namespace berthott\SX\Http\Requests;

use berthott\SX\Facades\Sxable;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    private string $target;

    public function __construct()
    {
        $this->target = Sxable::getTarget();
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * Unique fields will be validated unique.
     * All other fields will be validated nullable.
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
