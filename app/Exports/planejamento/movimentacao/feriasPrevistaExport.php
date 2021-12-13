<?php


namespace App\Exports\planejamento\movimentacao;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class feriasPrevistaExport implements FromView
{
    private $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function view(): View
    {
        $data = $this->data;

        return view('excel.planejamento.movimentacao.feriasPrevista', compact('data'));
    }


}
