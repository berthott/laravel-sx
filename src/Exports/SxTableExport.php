<?php

namespace berthott\SX\Exports;

use Facades\berthott\SX\Helpers\SxHelpers;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Variable Sheet Export for any table.
 * 
 * @link https://docs.laravel-excel.com/3.1/exports/collection.html
 * @link https://docs.laravel-excel.com/3.1/exports/mapping.html#adding-a-heading-row
 * @link https://docs.laravel-excel.com/3.1/exports/collection.html#strict-null-comparisons
 */
class SxTableExport implements FromCollection, WithHeadings, WithTitle, WithStrictNullComparison
{
    public function __construct(
        private string $tableName, 
        private array $ids = []
    ) {}
    
    /**
     * Show requested table name.
     */
    public function title(): string
    {
        return $this->tableName;
    }

    /**
     * Get a resource collection for all requested IDs and exclude
     * excluded columns.
     * 
     * @see \berthott\SX\Helpers\SxHelpers::getLabeledResource()
     * @see \berthott\SX\Exports\SxLabeledExport::collection()
     * @see \berthott\SX\Exports\SxTableExport::headings()
     */
    public function collection(): Collection
    {
        $query = DB::table($this->tableName)->select(...$this->headings());
        $collection = !empty($this->ids) ? $query->whereIn(config('sx.primary'), $this->ids)->get() : $query->get();
        return !$collection->count() || !property_exists($collection->first(), 'variableName')
            ? $collection
            : $collection->filter(function ($element) {
                return !in_array($element->variableName, config('sx.excludeFromExport'));
            });
    }

    /**
     * Filter columns if they are excluded from export.
     * 
     * @see \berthott\SX\Helpers\SxHelpers::getLabeledResource()
     * @see \berthott\SX\Exports\SxTableExport::collection()
     * @see \berthott\SX\Exports\SxLabeledExport::headings()
     */
    public function headings(): array
    {
        return array_filter(SxHelpers::getSortedColumns($this->tableName), function ($column) {
            return !in_array($column, config('sx.excludeFromExport'));
        });
    }

    /**
     * Format the columns.
     * 
     * This is intentionally disabled right now by not applying the WithColumnFormatting Concern.
     * @link https://docs.laravel-excel.com/3.1/exports/column-formatting.html#formatting-columns
     */
    public function columnFormats(): array
    {
        $i = 1;
        return array_reduce($this->headings(), function ($reduced, $column) use (&$i) {
            $type = DB::getSchemaBuilder()->getColumnType($this->tableName, $column);
            switch ($type) {
                case 'integer':
                    $reduced[Coordinate::stringFromColumnIndex($i)] = NumberFormat::FORMAT_NUMBER;
                    break;
            }
            $i++;
            return $reduced;
        }, []);
        ;
    }
}
