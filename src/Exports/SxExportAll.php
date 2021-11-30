<?php

namespace berthott\SX\Exports;

use berthott\SX\Exports\SxExport;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SxExportAll implements WithMultipleSheets
{
    use Exportable;

    private string $target;

    public function __construct(string $target)
    {
        $this->target = $target;
    }
    
    public function sheets(): array
    {
        return [
          new SxExport($this->target::entityTableName()),
          new SxExport($this->target::entityTableName().'_long'),
          new SxExport($this->target::singleName().'_questions'),
          new SxExport($this->target::singleName().'_labels'),
        ];
    }
}
