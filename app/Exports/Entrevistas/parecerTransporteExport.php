<?php


namespace App\Exports\Entrevistas;


use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class parecerTransporteExport implements FromView
{
    private $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function view(): View
    {
        $data = $this->data;

        return view('excel.entrevista.parecerTransporte', compact('data'));
    }


}
