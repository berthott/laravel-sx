<?php

namespace berthott\SX\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
          'query.distributionTs' => 'nullable|date',
          'query.reminder1Ts' => 'nullable|date',
          'query.reminder2Ts' => 'nullable|date',
          'query.channel' => 'nullable|string|in:Email,SMS,Eboks,Eboksbusiness',
          'query.customKey' => 'nullable|string',
          'form_params.email' => 'required|email',
          'form_params.*' => 'nullable',
        ];
    }
}
