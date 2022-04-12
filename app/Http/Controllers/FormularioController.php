<?php

namespace App\Http\Controllers;

use App\Models\Formulario;
use App\Models\FormularioResposta;
use Illuminate\Http\Request;

class FormularioController extends Controller
{
    public function carrega(Request $request, Formulario $formulario)
    {
        return $formulario->load('Setores.Alternativas.Opcoes');
    }

    public function buscaFormulario($tipo)
    {
        return Formulario::whereTitulo($tipo)->first()->load('Setores.Alternativas.Opcoes');
    }

    public function carregaResposta(Request $request)
    {
        if ($request->filled('user_id') && $request->filled('formulario')) {
            $resposta = FormularioResposta::whereUserId($request->user_id)->whereFormularioId($request->formulario);
            if ($resposta->count() > 0) {
                return [
                    'result' => $resposta->with('Formulario.Setores.Alternativas.Opcoes')->first(),
                    'tipo' => 'update'
                ];
            }
            return [
                'tipo' => 'cadastrar'
            ];
        } else {
            return response()->json(['msg' => "Erro -> Faltando parametros"], 400);
        }
    }

//    public function carregaResposta(Request $request, FormularioResposta $resposta, $usuario_id = null)
//    {
//        if ($usuario_id){
//        }
//
//        return $resposta->load('Formulario.Setores.Alternativas.Opcoes');
//    }

}
