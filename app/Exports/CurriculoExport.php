<?php


namespace App\Exports;


use App\Models\Curriculo;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class CurriculoExport implements FromView
{

    public function view(): View
    {
        $data = Curriculo::with(
            'Qualificacoes',
            'Experiencias',
            'Vaga',
            'Formacao',
            'Telefones',
            'Usuario',
            'FeedBack'
        )->get();

        return view('excel.curriculo', compact('data'));
    }


}
