<?php


namespace App\Exports\Entrevistas;


use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class resultadoIntegradoExport implements FromView
{
    private $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function view(): View
    {
        $data = $this->data;

//        $data = FeedbackCurriculo::with('Curriculo',
//            'Curriculo.Formacao',
//            'Cliente',
//            'VagaSelecionada',
//            'parecerRh',
//            'parecerTecnica',
//            'parecerRota',
//            'parecerTeste',
//            'TelPrincipal')->has('parecerRh')
//            ->has('parecerTecnica')
//            ->has('parecerRota')
//            ->has('parecerTeste')
//            ->whereIn('selecionado', ['sim', 'standby'])->whereInteresse(true)
//            ->get();
        return view('excel.entrevista.resultadoIntegrado', compact('data'));
    }


}
