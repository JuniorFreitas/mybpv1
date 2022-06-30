<?php

namespace App\Http\Controllers;

use App\Models\Afastamento;
use App\Models\Arquivo;
use App\Models\FeedbackCurriculo;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class AfastamentoController extends Controller
{
    public function store(Request $request)
    {
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados, [
            'afastamento.*.motivo' => 'required',
            'afastamento.*.periodo' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar Afastamento',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();//
                foreach ($dados['afastamento'] as $afastamento) {
                    if(isset($afastamento['novo'])){
                        $afastamentoAdm = Afastamento::create($afastamento);
                            foreach ($afastamento['anexosDel'] as $id_anexo) {
                                $arquivo = Arquivo::find($id_anexo);
                                $arquivo->excluir();
                            }
                            foreach ($afastamento['anexos'] as $index => $anexo) {
                                $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                                if ($arquivo) {
                                    $arquivo->temporario = false;
                                    $arquivo->chave = '';
                                    $arquivo->save();
                                    $afastamentoAdm->Anexos()->attach($arquivo->id);
                                }
                            }
                    }
                }
                DB::commit();
                return response()->json([]);
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
        $feedback->load(['Afastamentos' => function($query){
            $query->with('Anexos')->orderBy('id', 'desc');
        }]);
        $feedback->hoje = (new DataHora())->dataCompleta();
        return $feedback;
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
