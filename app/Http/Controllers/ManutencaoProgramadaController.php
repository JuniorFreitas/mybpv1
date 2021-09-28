<?php

namespace App\Http\Controllers;

use App\Models\ManutencaoProgramada;
use Illuminate\Http\Request;

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

    public function update(Request $request)
    {

        $dadosValidados = \Validator::make($request->input(), [
            'datahora' => "required|date_format:d/m/Y H:i",
        ]);
        if($dadosValidados->fails()){ // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar',
                'erros' => $dadosValidados->errors()
            ],400);
        }else{

            $manutencao = ManutencaoProgramada::first();
            $manutencao->ativo = !$manutencao->ativo;
            $manutencao->datahora = $request->datahora;
            $manutencao->save();
            $manutencao->refresh();

            //$manutencao->update($request->only(['datahora']));
            return response()->json($manutencao,201);
        }
    }
}
