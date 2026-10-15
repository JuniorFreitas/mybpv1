<?php

namespace App\Domain\Recrutamento;

use App\Models\CartaOferta;
use App\Models\EtapaTipo;
use App\Models\Etapas;
use App\Models\FeedbackCurriculo;
use App\Models\FeedbackPreadmissao;
use Illuminate\Support\Collection;

class GateEtapaSeletivo
{
    public function __construct(
        private readonly WorkflowSeletivoService $workflow
    ) {
    }

    public function etapasDoFeedback(FeedbackCurriculo $feedback, bool $somenteAtivas = true): Collection
    {
        $empresaId = (int) ($feedback->empresa_id ?? auth()->user()?->empresa_id);
        $vagaId = $feedback->vaga_id ? (int) $feedback->vaga_id : null;

        return $this->workflow->resolverEtapas($empresaId, $vagaId, $somenteAtivas);
    }

    public function concluida(FeedbackCurriculo $feedback, EtapaTipo $etapa): bool
    {
        if ($etapa->tipo === WorkflowSeletivoPadrao::TIPO_SISTEMA) {
            return match ($etapa->chave) {
                WorkflowSeletivoPadrao::CHAVE_CARTA => $this->cartaConcluida($feedback),
                WorkflowSeletivoPadrao::CHAVE_PREADMISSAO => $this->preadmissaoConcluida($feedback),
                WorkflowSeletivoPadrao::CHAVE_TECNICA => (bool) $feedback->parecerTecnica,
                WorkflowSeletivoPadrao::CHAVE_ROTA => (bool) $feedback->parecerRota,
                WorkflowSeletivoPadrao::CHAVE_APTO => $this->aptoConcluido($feedback, $etapa),
                default => $this->etapaManualConcluida($feedback, $etapa),
            };
        }

        return $this->etapaManualConcluida($feedback, $etapa);
    }

    public function podeAvancar(FeedbackCurriculo $feedback, EtapaTipo $passoAlvo): array
    {
        if ($feedback->status === 'desclassificado') {
            return ['ok' => false, 'motivo' => 'Candidato desclassificado'];
        }

        if ($passoAlvo->depende_de_id) {
            $dep = EtapaTipo::find($passoAlvo->depende_de_id);
            if ($dep && !$this->concluida($feedback, $dep)) {
                return [
                    'ok' => false,
                    'motivo' => 'Etapa dependente pendente: ' . $dep->nome,
                    'depende_de' => $dep->only(['id', 'chave', 'nome']),
                ];
            }
        }

        return ['ok' => true, 'motivo' => null];
    }

    /**
     * @return list<array{id:int,chave:string,nome:string}>
     */
    public function pendenciasObrigatorias(FeedbackCurriculo $feedback): array
    {
        $etapas = $this->etapasDoFeedback($feedback, true)
            ->where('obrigatoria', true)
            ->where('tipo', '!=', WorkflowSeletivoPadrao::TIPO_DESCLASSIFICACAO);

        $pendencias = [];
        foreach ($etapas as $etapa) {
            if (!$this->concluida($feedback, $etapa)) {
                $pendencias[] = [
                    'id' => (int) $etapa->id,
                    'chave' => (string) $etapa->chave,
                    'nome' => (string) $etapa->nome,
                ];
            }
        }

        return $pendencias;
    }

    public function prontoParaAdmissao(FeedbackCurriculo $feedback): bool
    {
        if ($feedback->status === 'desclassificado') {
            return false;
        }

        return count($this->pendenciasObrigatorias($feedback)) === 0;
    }

    /**
     * Payload enxuto para UI (com flags de liberação/conclusão).
     */
    public function montarProgresso(FeedbackCurriculo $feedback, ?Collection $etapasVisiveis = null): array
    {
        $todas = $this->etapasDoFeedback($feedback, true);
        $visiveis = $etapasVisiveis ?? $todas;

        $itens = [];
        foreach ($visiveis as $etapa) {
            $gate = $this->podeAvancar($feedback, $etapa);
            $itens[] = [
                'id' => (int) $etapa->id,
                'chave' => $etapa->chave,
                'nome' => $etapa->nome,
                'tipo' => $etapa->tipo,
                'ordem' => (int) $etapa->ordem,
                'obrigatoria' => (bool) $etapa->obrigatoria,
                'is_apto_admissao' => (bool) $etapa->is_apto_admissao,
                'habilidade' => $etapa->habilidade,
                'concluida' => $this->concluida($feedback, $etapa),
                'liberada' => (bool) $gate['ok'],
                'motivo_bloqueio' => $gate['motivo'],
            ];
        }

        return [
            'etapas' => $itens,
            'pendencias_obrigatorias' => $this->pendenciasObrigatorias($feedback),
            'pronto_para_admissao' => $this->prontoParaAdmissao($feedback),
        ];
    }

    private function etapaManualConcluida(FeedbackCurriculo $feedback, EtapaTipo $etapa): bool
    {
        $q = Etapas::query()->where('feedback_id', $feedback->id);

        if ($etapa->tipo === WorkflowSeletivoPadrao::TIPO_DESCLASSIFICACAO) {
            return $q->where('status', 'desclassificado')
                ->where(function ($qq) use ($etapa) {
                    $qq->where('passo_id', $etapa->id)->orWhere('etapa', $etapa->nome);
                })
                ->exists();
        }

        return $q->where('status', 'classificado')
            ->where(function ($qq) use ($etapa) {
                $qq->where('passo_id', $etapa->id)->orWhere('etapa', $etapa->nome);
            })
            ->exists();
    }

    private function aptoConcluido(FeedbackCurriculo $feedback, EtapaTipo $etapa): bool
    {
        if ($this->etapaManualConcluida($feedback, $etapa)) {
            return true;
        }

        return Etapas::query()
            ->where('feedback_id', $feedback->id)
            ->where('status', 'classificado')
            ->where(function ($q) {
                $q->where('etapa', 'Apto para Admissao')
                    ->orWhere('etapa', 'Apto para Admissão');
            })
            ->exists();
    }

    private function cartaConcluida(FeedbackCurriculo $feedback): bool
    {
        $carta = CartaOferta::query()
            ->where(function ($q) use ($feedback) {
                $q->where('feedback_id', $feedback->id)
                    ->orWhere(function ($qq) use ($feedback) {
                        $qq->where('curriculo_id', $feedback->curriculo_id)
                            ->whereNull('feedback_id');
                    });
            })
            ->latest('id')
            ->first();

        if (!$carta) {
            return false;
        }

        return $carta->status === CartaOferta::STATUS_ACEITO_RH;
    }

    private function preadmissaoConcluida(FeedbackCurriculo $feedback): bool
    {
        return FeedbackPreadmissao::query()
            ->where('feedback_id', $feedback->id)
            ->exists();
    }
}
