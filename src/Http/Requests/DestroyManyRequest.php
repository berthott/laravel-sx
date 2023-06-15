<?php

namespace berthott\SX\Http\Requests;

use Facades\berthott\SX\Services\SxableService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Simple FormRequest Implementation for validation.
 */
class DestroyManyRequest extends FormRequest
{
    private string $target;

    public function __construct()
    {
        $this->target = SxableService::getTarget();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'ids' => 'required',
            'ids.*' => 'exists:'.$this->target::entityTableName().','.config('sx.primary'),
        ];
    }
}
