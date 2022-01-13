<?php

namespace berthott\SX\Exports;

use berthott\SX\Exports\SxTableExport;
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
          new SxLabeledExport($this->target),
          new SxTableExport($this->target::entityTableName()),
          //new SxExport($this->target::entityTableName().'_long'),
          new SxTableExport($this->target::singleName().'_questions'),
          new SxTableExport($this->target::singleName().'_labels'),
        ];
    }
}
