<?php


namespace App\Exports\admissao\apontamento;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class cihExport implements FromView
{
    private $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function view(): View
    {
        $data = $this->data;

        return view('excel.admissao.apontamento.cih', compact('data'));
    }

}
