<?php

namespace App\Services\ControleExames;

use App\Models\Admissao;
use App\Models\AlternativaFormulario;
use App\Models\ExameTipo;
use App\Models\FeedbackCurriculo;
use App\Models\RespostaAlternativas;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RestricaoOrdemExameDemitido
{
    public const MENSAGEM = 'Para colaborador demitido, o tipo de ordem deve ser Demissional.';

    public function colaboradorDemitido(int $feedbackId): bool
    {
        if ($feedbackId <= 0) {
            return false;
        }

        $query = FeedbackCurriculo::query()
            ->whereKey($feedbackId)
            ->whereHas('Admissao', function ($admissao) {
                $admissao->where('status', Admissao::STATUS_DEMITIDO);
            })
            ->has('Demissao');

        $empresaId = auth()->user()?->empresa_id;
        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }

        return $query->exists();
    }

    /**
     * @param  Collection<int, ExameTipo|object>  $tipos
     * @return Collection<int, ExameTipo|object>
     */
    public function filtrarTipos(Collection $tipos, bool $demitido): Collection
    {
        if (!$demitido) {
            return $tipos->values();
        }

        return $tipos
            ->filter(fn ($tipo) => $this->ehDemissional($tipo->label ?? null))
            ->values();
    }

    public function encaminhamentoPermitido(int $feedbackId, Request $request): bool
    {
        if (!$this->colaboradorDemitido($feedbackId)) {
            return true;
        }

        return $this->ehDemissional($this->labelInformado($request));
    }

    public function ehDemissional(?string $label): bool
    {
        return mb_strtolower(trim((string) $label)) === mb_strtolower(ExameTipo::LABEL_DEMISSIONAL);
    }

    private function labelInformado(Request $request): ?string
    {
        if ($this->usaTipoDoSelect($request)) {
            return ExameTipo::query()->find($request->input('exame_tipo_id'))?->label;
        }

        $empresaId = auth()->user()?->empresa_id;
        if (!$empresaId) {
            return null;
        }

        $tipoOrdem = AlternativaFormulario::query()
            ->where('nome', 'Tipo de ordem')
            ->where('empresa_id', $empresaId)
            ->first();

        if (!$tipoOrdem) {
            return null;
        }

        $valor = data_get($request->input('respostas'), 'alternativa_id_' . $tipoOrdem->id . '.valor');

        return RespostaAlternativas::query()->where('value', $valor)->first()?->label;
    }

    /**
     * Mesma condição do encaminhamento com PCMSO: o tipo vem do select exame_tipo_id.
     */
    private function usaTipoDoSelect(Request $request): bool
    {
        return $request->filled('pcmso_id');
    }
}
