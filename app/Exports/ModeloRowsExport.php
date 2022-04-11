<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ModeloRowsExport implements FromArray, WithHeadings
{
    protected $headdings;
    protected $rows;

    public function __construct($headdings, $rows)
    {
        $this->headdings = $headdings;
        $this->rows = $rows;
    }

    public function headings(): array
    {
        return $this->headdings;
    }


    public function array(): array
    {
        return $this->rows;
    }
}
