<?php

namespace App\Domain\SaudeOcupacional\Pcmso;

use App\Domain\SaudeOcupacional\ModuloSaudeOcupacional;
use App\Domain\SaudeOcupacional\Regras\PerfisColaboradoresRepositorio;
use App\Domain\SaudeOcupacional\Regras\Periodicidade;
use App\Models\PcmsoVersao;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Exames complementares previstos no PCMSO vigente para um colaborador (cargo vigente) e tipo de ASO.
 * Usado na ficha de encaminhamento enviada à clínica. Só nomes de exame (sem dado de saúde).
 */
class ExamesPrevistosPcmso
{
    public function __construct(private readonly PerfisColaboradoresRepositorio $perfis)
    {
    }

    /**
     * @return array{versao: int, vigencia_inicio_br: string|null, exames: list<array{label: string, obrigatorio: bool, periodicidade: string|null}>}|null
     */
    public function para(int $empresaId, ?int $pcmsoId, ?int $feedbackId, ?int $exameTipoId, ?CarbonImmutable $data = null): ?array
    {
        if (!$pcmsoId || !$feedbackId || !$exameTipoId || !Schema::hasTable(PcmsoVersao::TABELA)) {
            return null;
        }
        if (!ModuloSaudeOcupacional::habilitado($empresaId)) {
            return null;
        }

        $dia = ($data ?? CarbonImmutable::today())->toDateString();
        $versao = DB::table('pcmsos_versoes')
            ->where('empresa_id', $empresaId)
            ->where('pcmso_id', $pcmsoId)
            ->whereIn('status', [PcmsoVersao::STATUS_VIGENTE, PcmsoVersao::STATUS_ARQUIVADO])
            ->whereNull('deleted_at')
            ->whereDate('vigencia_inicio', '<=', $dia)
            ->where(fn ($q) => $q->whereNull('vigencia_fim')->orWhere('vigencia_fim', '>=', $dia))
            ->orderByDesc('versao')
            ->first(['id', 'versao', 'vigencia_inicio']);
        if (!$versao) {
            return null;
        }

        $vagaId = $this->perfis->carregar($feedbackId)?->vagaId;
        if (!$vagaId) {
            return null;
        }

        $exames = DB::table('pcmsos_versoes_exames as pe')
            ->join('exames as e', 'e.id', '=', 'pe.exame_id')
            ->where('pe.pcmso_versao_id', $versao->id)
            ->where('pe.vaga_id', $vagaId)
            ->orderByDesc('pe.obrigatorio')
            ->orderBy('e.label')
            ->get(['e.label', 'pe.exame_tipo_ids', 'pe.obrigatorio', 'pe.unidade', 'pe.valor'])
            ->filter(fn ($l) => in_array($exameTipoId, array_map('intval', (array) json_decode((string) $l->exame_tipo_ids, true)), true))
            ->map(fn ($l) => [
                'label' => (string) $l->label,
                'obrigatorio' => (bool) $l->obrigatorio,
                'periodicidade' => $l->unidade && $l->valor ? (new Periodicidade($l->unidade, (int) $l->valor))->descricao() : null,
            ])
            ->values()
            ->all();

        return [
            'versao' => (int) $versao->versao,
            'vigencia_inicio_br' => $versao->vigencia_inicio ? CarbonImmutable::parse($versao->vigencia_inicio)->format('d/m/Y') : null,
            'exames' => $exames,
        ];
    }
}
