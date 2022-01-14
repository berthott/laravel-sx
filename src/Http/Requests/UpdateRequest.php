<?php

namespace berthott\SX\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
          'form_params.email' => 'nullable|email',
          'form_params.*' => 'nullable',
        ];
    }
}
