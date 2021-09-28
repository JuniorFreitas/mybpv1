<?php

namespace App\Http\Controllers;

use App\Models\HorarioAcesso;
use Illuminate\Http\Request;

class HorarioAcessoController extends Controller
{
    public function index()
    {
        $acesso = HorarioAcesso::first();

        return view('g.configuracoes.horario-acesso.index',compact('acesso'));
    }

    public function init(){
        $acesso = HorarioAcesso::first();
        return response()->json($acesso,201);
    }

    public function ativaDesativa(){
        $acesso = HorarioAcesso::first();
        $acesso->ativo = !$acesso->ativo;
        $acesso->save();
        $acesso->refresh();
        return response()->json($acesso,201);
    }

    public function update(Request $request)
    {
        $acesso = HorarioAcesso::first();

        $dadosValidados = \Validator::make($request->input(), [
            'abertura' => "required|date_format:H:i",
            'fechamento' => 'required|date_format:H:i',
        ]);
        if($dadosValidados->fails()){ // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar',
                'erros' => $dadosValidados->errors()
            ],400);
        }else{
            $acesso->update($request->only(['abertura','fechamento']));
            return response()->json($acesso,201);
        }
    }



}
