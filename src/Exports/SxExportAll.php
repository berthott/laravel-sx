<?php

namespace berthott\SX\Exports;

use berthott\SX\Exports\SxTableExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Multiple Sheets Export.
 * 
 * @link https://docs.laravel-excel.com/3.1/exports/multiple-sheets.html
 */
class SxExportAll implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private string $target, 
        private array $ids = []
    ) {}
    
    /**
     * Gather different sheets.
     */
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
