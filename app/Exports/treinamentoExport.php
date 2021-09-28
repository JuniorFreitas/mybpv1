<?php


namespace App\Exports;


use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class treinamentoExport implements FromView
{
//    use Exportable;

    private $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function view(): View
    {
        $data = $this->data;
        return view('excel.portaria.exportExcel', compact('data'));
    }

}
