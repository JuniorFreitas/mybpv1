<?php

namespace App\Http\Controllers;

use App\Mail\DesclassificacaoMail;
use App\Mail\EtapaProvaManualMail;
use App\Mail\ProximaAptoAdmissaoMail;
use App\Models\Etapas;
use App\Models\FeedbackCurriculo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EtapasController extends Controller
{
    public function desclassificar(Request $request,  FeedbackCurriculo $feedback)
    {
        try {
            \DB::beginTransaction();
            Etapas::create([
                'feedback_id' => $feedback->id,
                'curriculo_id' => $feedback->curriculo_id,
                'user_id' => auth()->id(),
                'vaga_id' => $feedback->vaga_id,
                'etapa' => $request->etapa,
                'enviado_email' => $request->enviarEmail,
                'text_email' => '',
                'observacao' => $request->observacao,
                'preenchido_por' => $request->preenchido_por,
                'status' => 'desclassificado',
            ]);

            \DB::commit();

            if ($request->enviarEmail){
                \Mail::send(new DesclassificacaoMail([
                    'nome' => $feedback->Curriculo->nome,
                    'email' => $feedback->Curriculo->email,
                ]));
            }

            return response()->json("ok", 201);
        } catch (\Exception $e) {
            \Log::debug("error ao modificar Etapa:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome);
            DB::rollback();
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function classificar(Request $request, FeedbackCurriculo $feedback)
    {
        try {
            \DB::beginTransaction();
            Etapas::create([
                'feedback_id' => $feedback->id,
                'curriculo_id' => $feedback->curriculo_id,
                'user_id' => auth()->id(),
                'vaga_id' => $feedback->vaga_id,
                'etapa' => $request->etapa,
                'enviado_email' => $request->enviarEmail,
                'text_email' => $request->mensagem,
                'observacao' => $request->observacao,
                'preenchido_por' => $request->preenchido_por,
                'status' => 'classificado',
            ]);

            \DB::commit();
            if ($request->enviarEmail){
                if ($request->etapa == 'Apto para Admissao'){
                    \Mail::send(new ProximaAptoAdmissaoMail([
                        'nome' => $feedback->Curriculo->nome,
                        'email' => $feedback->Curriculo->email,
                        'mensagem' => $request->mensagem,
                        'etapa' => $request->etapa
                    ]));
                }else{
                    \Mail::send(new EtapaProvaManualMail([
                        'nome' => $feedback->Curriculo->nome,
                        'email' => $feedback->Curriculo->email,
                        'mensagem' => $request->mensagem,
                        'etapa' => $request->etapa
                    ]));
                }
            }

            return response()->json('ok', 201);
        } catch (\Exception $e) {
            $msg = "error ao modificar Etapa:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            DB::rollback();
            return response()->json(['msg' => $msg], 400);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }
}
