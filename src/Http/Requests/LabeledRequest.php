<?php

namespace berthott\SX\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LabeledRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
          'labeled' => 'nullable|boolean',
        ];
    }
}
