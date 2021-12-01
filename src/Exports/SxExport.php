<?php

namespace berthott\SX\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SxExport implements FromCollection, WithHeadings, WithTitle, WithStrictNullComparison
{
    private string $tableName;

    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }
    
    public function title(): string
    {
        return $this->tableName;
    }

    public function collection(): Collection
    {
        return DB::table($this->tableName)->get();
    }

    public function headings(): array
    {
        return Schema::getColumnListing($this->tableName);
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
