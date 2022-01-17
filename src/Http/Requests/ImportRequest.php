<?php

namespace berthott\SX\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
          'fresh' => 'nullable|boolean',
          'labeled' => 'nullable|boolean',
        ];
    }
}
