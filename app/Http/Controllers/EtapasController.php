<?php

namespace App\Http\Controllers;

use App\Domain\Recrutamento\GateEtapaSeletivo;
use App\Domain\Recrutamento\WorkflowSeletivoPadrao;
use App\Domain\Recrutamento\WorkflowSeletivoService;
use App\Mail\DesclassificacaoMail;
use App\Mail\EtapaProvaManualMail;
use App\Mail\ProximaAptoAdmissaoMail;
use App\Models\EtapaTipo;
use App\Models\Etapas;
use App\Models\FeedbackCurriculo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EtapasController extends Controller
{
    public function __construct(
        private readonly WorkflowSeletivoService $workflow,
        private readonly GateEtapaSeletivo $gate
    ) {
    }

    public function desclassificar(Request $request, FeedbackCurriculo $feedback)
    {
        try {
            $etapaTipo = $this->resolverPasso($request, $feedback, WorkflowSeletivoPadrao::TIPO_DESCLASSIFICACAO);
            if ($etapaTipo instanceof \Illuminate\Http\JsonResponse) {
                return $etapaTipo;
            }

            DB::beginTransaction();
            Etapas::create([
                'feedback_id' => $feedback->id,
                'curriculo_id' => $feedback->curriculo_id,
                'user_id' => auth()->id(),
                'vaga_id' => $feedback->vaga_id,
                'etapa' => $etapaTipo->nome,
                'passo_id' => $etapaTipo->id,
                'enviado_email' => $request->enviarEmail,
                'text_email' => '',
                'observacao' => $request->observacao,
                'preenchido_por' => $request->preenchido_por,
                'status' => 'desclassificado',
            ]);

            DB::commit();

            if ($request->enviarEmail && $etapaTipo->permite_email) {
                \Mail::send(new DesclassificacaoMail([
                    'nome' => $feedback->Curriculo->nome,
                    'email' => $feedback->Curriculo->email,
                ]));
            }

            return response()->json('ok', 201);
        } catch (\Exception $e) {
            \Log::debug("error ao modificar Etapa:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome);
            DB::rollback();

            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function classificar(Request $request, FeedbackCurriculo $feedback)
    {
        try {
            $etapaTipo = $this->resolverPasso($request, $feedback, null);
            if ($etapaTipo instanceof \Illuminate\Http\JsonResponse) {
                return $etapaTipo;
            }

            if ($etapaTipo->tipo === WorkflowSeletivoPadrao::TIPO_DESCLASSIFICACAO) {
                return response()->json(['msg' => 'Use a ação de desclassificar para esta etapa'], 422);
            }

            // Etapas de sistema (exceto apto) concluem via módulo respectivo
            if ($etapaTipo->tipo === WorkflowSeletivoPadrao::TIPO_SISTEMA
                && $etapaTipo->chave !== WorkflowSeletivoPadrao::CHAVE_APTO
                && !$etapaTipo->is_apto_admissao) {
                return response()->json([
                    'msg' => 'Esta etapa é concluída pelo módulo: ' . $etapaTipo->nome,
                ], 422);
            }

            $gate = $this->gate->podeAvancar($feedback, $etapaTipo);
            if (!$gate['ok']) {
                return response()->json(['msg' => $gate['motivo']], 422);
            }

            DB::beginTransaction();
            Etapas::create([
                'feedback_id' => $feedback->id,
                'curriculo_id' => $feedback->curriculo_id,
                'user_id' => auth()->id(),
                'vaga_id' => $feedback->vaga_id,
                'etapa' => $etapaTipo->nome,
                'passo_id' => $etapaTipo->id,
                'enviado_email' => $request->enviarEmail,
                'text_email' => $request->mensagem,
                'observacao' => $request->observacao,
                'preenchido_por' => $request->preenchido_por,
                'status' => 'classificado',
            ]);

            DB::commit();

            $isApto = $etapaTipo->is_apto_admissao
                || $etapaTipo->chave === WorkflowSeletivoPadrao::CHAVE_APTO
                || $etapaTipo->nome === 'Apto para Admissao';

            if ($request->enviarEmail && $etapaTipo->permite_email) {
                if ($isApto) {
                    \Mail::send(new ProximaAptoAdmissaoMail([
                        'nome' => $feedback->Curriculo->nome,
                        'email' => $feedback->Curriculo->email,
                        'mensagem' => $request->mensagem,
                        'etapa' => $etapaTipo->nome,
                    ]));
                } else {
                    \Mail::send(new EtapaProvaManualMail([
                        'nome' => $feedback->Curriculo->nome,
                        'email' => $feedback->Curriculo->email,
                        'mensagem' => $request->mensagem,
                        'etapa' => $etapaTipo->nome,
                    ]));
                }
            }

            return response()->json('ok', 201);
        } catch (\Exception $e) {
            $msg = "error ao modificar Etapa:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            DB::rollback();

            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Resolve EtapaTipo no escopo do feedback (cargo ou empresa) e valida permissão.
     *
     * @return EtapaTipo|\Illuminate\Http\JsonResponse
     */
    private function resolverPasso(Request $request, FeedbackCurriculo $feedback, ?string $tipoEsperado)
    {
        $user = auth()->user();
        $empresaId = (int) ($feedback->empresa_id ?: $user->empresa_id);
        $vagaId = $feedback->vaga_id ? (int) $feedback->vaga_id : null;

        $etapas = $this->workflow->resolverEtapas($empresaId, $vagaId, true);
        $passoId = $request->filled('passo_id') ? (int) $request->passo_id : null;

        $etapaTipo = null;
        if ($passoId) {
            $etapaTipo = $etapas->firstWhere('id', $passoId);
        }

        // Compatibilidade: nome sem passo_id
        if (!$etapaTipo && $request->filled('etapa')) {
            $etapaTipo = $etapas->first(function (EtapaTipo $e) use ($request) {
                return $e->nome === $request->etapa;
            });
        }

        if (!$etapaTipo) {
            return response()->json(['msg' => 'Etapa inválida para o workflow deste cargo'], 422);
        }

        if ($tipoEsperado && $etapaTipo->tipo !== $tipoEsperado) {
            return response()->json(['msg' => 'Tipo de etapa incompatível'], 422);
        }

        $visiveis = $this->workflow->filtrarPorPermissao(collect([$etapaTipo]), $user);
        if ($visiveis->isEmpty()) {
            return response()->json(['msg' => 'Sem permissão para esta etapa'], 403);
        }

        return $etapaTipo;
    }
}
