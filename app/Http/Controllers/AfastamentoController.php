<?php

namespace App\Http\Controllers;

use App\Models\Afastamento;
use App\Models\Arquivo;
use App\Models\FeedbackCurriculo;
use DB;
use Illuminate\Http\Request;

class AfastamentoController extends Controller
{
    public function store(Request $request)
    {
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados, [
            'motivo' => 'required',
            'data_inicio' => 'required',
            'data_fim' => 'required'
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar Afastamento',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $dados['quem_cadastrou'] = auth()->id();

                Afastamento::create($dados);

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error CADASTRO DE AFASTAMENTO HISTORICO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
            }
        }
    }

    public function show(FeedbackCurriculo $feedback)
    {
        return $feedback->load('Afastamentos');
    }

    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::DISCO_AFASTAMENTO);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_AFASTAMENTO, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_AFASTAMENTO, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_AFASTAMENTO, $arquivo);
    }
}
