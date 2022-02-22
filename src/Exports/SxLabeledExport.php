<?php

namespace berthott\SX\Exports;

use berthott\SX\Models\Resources\SxableLabeledExportResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class SxLabeledExport implements FromCollection, WithHeadings, WithTitle, WithStrictNullComparison
{
    private string $target;
    private array $ids;

    public function __construct(string $target, array $ids = [])
    {
        $this->target = $target;
        $this->ids = $ids;
    }
    
    public function title(): string
    {
        return 'wide_labeled';
    }

    public function collection(): Collection | ResourceCollection
    {
        $entries = !empty($this->ids) ? $this->target::whereIn(config('sx.primary'), $this->ids)->get() : $this->target::all();
        return SxableLabeledExportResource::collection($entries);
    }

    public function headings(): array
    {
        return array_filter($this->target::labeledAttributes(), function ($column) {
            return !in_array($column, config('sx.excludeFromExport'));
        });
    }
}
