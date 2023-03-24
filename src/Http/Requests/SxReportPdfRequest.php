<?php

namespace berthott\SX\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SxReportPdfRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
          'filename' => 'required|string',
          'pageLimit' => 'nullable|integer',
          'pages' => 'required|array',
          'pages.*.data.*' => 'nullable|array:id,type,result,dimensions',
          'pages.*.data.pageHeight' => 'nullable|integer',
        ];
    }
}
