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

    public function update(\App\Http\Requests\Admin\UpdateHorarioAcessoRequest $request)
    {
        $acesso = HorarioAcesso::first();
        $acesso->update($request->only(['abertura', 'fechamento']));
        return response()->json($acesso, 201);
    }



}
