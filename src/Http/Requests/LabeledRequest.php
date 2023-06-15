<?php

namespace berthott\SX\Http\Requests;

use berthott\SX\Facades\Sxable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Simple FormRequest Implementation for validation.
 */
class LabeledRequest extends FormRequest
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
        return [
          'labeled' => 'nullable|boolean',
          'lang' => [
            'nullable',
            'string',
            Rule::in($this->target::surveyLanguages()),
          ],
        ];
    }
}
