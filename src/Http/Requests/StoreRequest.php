<?php

namespace berthott\SX\Http\Requests;

use berthott\SX\Facades\Sxable;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    private string $target;

    public function __construct()
    {
        $this->target = Sxable::getTarget();
    }

    /**
     * Get the validation rules that apply to the request.
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
