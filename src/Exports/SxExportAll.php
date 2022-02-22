<?php

namespace berthott\SX\Exports;

use berthott\SX\Exports\SxTableExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SxExportAll implements WithMultipleSheets
{
    use Exportable;

    private string $target;
    private array $ids;

    public function __construct(string $target, array $ids = [])
    {
        $this->target = $target;
        $this->ids = $ids;
    }
    
    public function sheets(): array
    {
        return [
          new SxLabeledExport($this->target, $this->ids),
          new SxTableExport($this->target::entityTableName(), $this->ids),
          //new SxExport($this->target::entityTableName().'_long'),
          new SxTableExport($this->target::singleName().'_questions'),
          new SxTableExport($this->target::singleName().'_labels'),
        ];
    }
}
