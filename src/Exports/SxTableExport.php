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

class SxTableExport implements FromCollection, WithHeadings, WithTitle, WithStrictNullComparison
{
    private string $tableName;
    private array $ids;

    public function __construct(string $tableName, array $ids = [])
    {
        $this->tableName = $tableName;
        $this->ids = $ids;
    }
    
    public function title(): string
    {
        return $this->tableName;
    }

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

    public function headings(): array
    {
        return array_filter(SxHelpers::getSortedColumns($this->tableName), function ($column) {
            return !in_array($column, config('sx.excludeFromExport'));
        });
    }

    // intentionally disabled because of missing WithColumnFormatting Concern
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
