<?php

namespace berthott\SX\Exports;

use berthott\SX\Models\Resources\SxableLabeledResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Fixed Sheet Export for wide labeled table.
 * 
 * @link https://docs.laravel-excel.com/3.1/exports/collection.html
 * @link https://docs.laravel-excel.com/3.1/exports/mapping.html#adding-a-heading-row
 */
class SxLabeledExport implements FromCollection, WithHeadings, WithTitle, WithStrictNullComparison
{
    public function __construct(
        private string $target, 
        private array $ids = []
    ) {}
    
    /**
     * Fixed title
     */
    public function title(): string
    {
        return 'wide_labeled';
    }

    /**
     * Get a labeled resource collection for all requested IDs
     */
    public function collection(): Collection | ResourceCollection
    {
        $entries = !empty($this->ids) ? $this->target::whereIn(config('sx.primary'), $this->ids)->get() : $this->target::all();
        return SxableLabeledResource::collection($entries);
    }

    /**
     * Filter columns if they are excluded from export.
     * 
     * @see \berthott\SX\Helpers\SxHelpers::getLabeledResource()
     * @see \berthott\SX\Exports\SxTableExport::collection()
     * @see \berthott\SX\Exports\SxTableExport::headings()
     */
    public function headings(): array
    {
        return array_filter($this->target::labeledAttributes(), function ($column) {
            return !in_array($column, config('sx.excludeFromExport'));
        });
    }
}
