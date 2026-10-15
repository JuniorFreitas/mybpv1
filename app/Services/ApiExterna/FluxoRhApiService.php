<?php

namespace App\Services\ApiExterna;

use App\Models\Admissao;
use App\Support\ApiExterna\ApiExternaId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FluxoRhApiService
{
    public function __construct(
        private readonly AdmissaoApiAssembler $assembler
    ) {
    }

    public function completo(int $empresaId, int $feedbackId): ?array
    {
        $payload = $this->assembler->montarUm($empresaId, $feedbackId, completo: true);
        if (! $payload) {
            return null;
        }

        $etapas = $this->montarEtapas($empresaId, $feedbackId, $payload);
        if ($etapas === null) {
            return null;
        }

        $payload['etapas'] = $etapas;

        return $payload;
    }

    /**
     * @return array{public_id: string, etapas: array<string, bool>, links: array<string, string>}|null
     */
    public function apenasEtapas(int $empresaId, int $feedbackId): ?array
    {
        $existe = DB::table('feedback_curriculos')
            ->where('empresa_id', $empresaId)
            ->where('id', $feedbackId)
            ->whereNull('deleted_at')
            ->exists();

        if (! $existe) {
            return null;
        }

        $etapas = $this->montarEtapas($empresaId, $feedbackId);
        if ($etapas === null) {
            return null;
        }

        $pid = ApiExternaId::encode($feedbackId);

        return [
            'public_id' => $pid,
            'etapas' => $etapas,
            'links' => [
                'self' => url('/api/v3/processos/' . $pid . '/etapas'),
                'processo' => url('/api/v3/processos/' . $pid),
                'fluxo' => url('/api/v3/fluxo/' . $pid),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $payloadCompleto
     * @return array<string, bool>|null
     */
    public function montarEtapas(int $empresaId, int $feedbackId, ?array $payloadCompleto = null): ?array
    {
        $query = DB::table('feedback_curriculos as fc')
            ->leftJoin('parecer_rh as pr', 'pr.feedback_id', '=', 'fc.id')
            ->leftJoin('resultado_integrados as ri', 'ri.feedback_id', '=', 'fc.id')
            ->leftJoin('admissoes as a', function ($join) {
                $join->on('a.feedback_id', '=', 'fc.id')->whereNull('a.deleted_at');
            })
            ->leftJoin('demissaos as d', 'd.feedback_id', '=', 'fc.id')
            ->where('fc.empresa_id', $empresaId)
            ->where('fc.id', $feedbackId)
            ->whereNull('fc.deleted_at')
            ->select([
                'pr.id as parecer_rh_id',
                'ri.id as resultado_id',
                'a.id as admissao_id',
                'a.status as admissao_status',
                'd.id as demissao_id',
            ]);

        if (Schema::hasTable('feedback_preadmissao')) {
            $query->leftJoin('feedback_preadmissao as pa', 'pa.feedback_id', '=', 'fc.id')
                ->addSelect('pa.id as pre_admissao_id');
        }

        $flags = $query->first();
        if (! $flags) {
            return null;
        }

        return [
            'curriculo' => true,
            'parecer_rh' => (bool) ($flags->parecer_rh_id ?? null),
            'resultado_integrado' => (bool) ($flags->resultado_id ?? null),
            'pre_admissao' => (bool) ($flags->pre_admissao_id ?? null)
                || ! empty($payloadCompleto['pre_admissao']),
            'admissao' => (bool) ($flags->admissao_id ?? null),
            'pos_admissao' => ($flags->admissao_status ?? null) === Admissao::STATUS_ADMISSAO_ADMITIDO,
            'demitido' => (bool) ($flags->demissao_id ?? null),
        ];
    }
}
