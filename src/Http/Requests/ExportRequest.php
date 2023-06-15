<?php

namespace berthott\SX\Http\Requests;

use Facades\berthott\SX\Services\SxableService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Simple FormRequest Implementation for validation.
 */
class ExportRequest extends FormRequest
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
          'table' => 'nullable|string|in:questions,labels,long,wide,wide_labeled',
          'ids' => 'nullable',
          'ids.*' => 'exists:'.$this->target::entityTableName().','.config('sx.primary'),
        ];
    }
}
