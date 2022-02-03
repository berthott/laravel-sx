<?php

namespace berthott\SX\Exports;

use berthott\SX\Models\Resources\SxableLabeledResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class SxLabeledExport implements FromCollection, WithHeadings, WithTitle, WithStrictNullComparison
{
    private string $target;

    public function __construct(string $target)
    {
        $this->target = $target;
    }
    
    public function title(): string
    {
        return 'wide_labeled';
    }

    public function collection(): Collection | ResourceCollection
    {
        return SxableLabeledResource::collection($this->target::all());
    }

    public function headings(): array
    {
        return array_filter($this->target::labeledAttributes(), function ($column) {
            return !in_array($column, config('sx.excludeFromExport'));
        });
    }
}
