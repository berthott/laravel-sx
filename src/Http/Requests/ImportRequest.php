<?php

namespace berthott\SX\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Simple FormRequest Implementation for validation.
 */
class ImportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
          'fresh' => 'nullable|boolean',
          'since' => 'nullable|string',
          'labeled' => 'nullable|boolean',
        ];
    }
}
