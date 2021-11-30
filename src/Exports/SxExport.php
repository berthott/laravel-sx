<?php

namespace berthott\SX\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SxExport implements FromCollection, WithHeadings, WithTitle
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
}
