<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\UpdateManutencaoProgramadaRequest;
use App\Models\ManutencaoProgramada;

class ManutencaoProgramadaController extends Controller
{
    public function index()
    {
        $manutencao = ManutencaoProgramada::first();

        return view('g.site.manutencao-programada.index',compact('manutencao'));
    }

    public function init(){
        $manutencao = ManutencaoProgramada::first();
        return response()->json($manutencao,201);
    }

    public function update(UpdateManutencaoProgramadaRequest $request)
    {
        $manutencao = ManutencaoProgramada::first();
        $manutencao->ativo = !$manutencao->ativo;
        $manutencao->datahora = $request->datahora;
        $manutencao->save();
        $manutencao->refresh();

        return response()->json($manutencao,201);
    }
}
