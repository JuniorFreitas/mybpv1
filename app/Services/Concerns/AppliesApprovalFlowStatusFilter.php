<?php

namespace App\Services\Concerns;

use App\Models\AprovacaoExtraConfig;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Filtro de status por etapa do fluxo (gestor → extra → RH).
 * Espelha o padrão usado na Transferência (pendente/aprovado/reprovado por etapa).
 *
 * @mixin object{user: \App\Models\User}
 */
trait AppliesApprovalFlowStatusFilter
{
    /**
     * @param  EloquentBuilder|QueryBuilder  $query
     * @param  array{gestor: string, extra: string, rh: string}  $columns
     */
    protected function applyApprovalFlowStatusFilter(
        EloquentBuilder|QueryBuilder $query,
        ?string $status,
        array $columns,
        string $aprovado,
        string $reprovado,
        string $tipoProcessoExtra
    ): void {
        if ($status === null || $status === '') {
            return;
        }

        $gestor = $columns['gestor'];
        $extra = $columns['extra'];
        $rh = $columns['rh'];

        // Em aberto = pendente na etapa atual (gestor, extra ou RH), como "reprovado" agrupa qualquer etapa.
        if ($status === 'aberto') {
            $this->whereApprovalFlowEmAndamento($query, $gestor, $extra, $rh, $reprovado);
            return;
        }

        if ($status === 'pendente_gestor') {
            $this->whereApprovalFlowEmAndamento($query, $gestor, $extra, $rh, $reprovado);
            $this->whereApprovalFlowCampoVazio($query, $gestor);
            return;
        }

        if ($status === 'aprovado_gestor') {
            $query->where($gestor, $aprovado);
            return;
        }

        if ($status === 'reprovado_gestor') {
            $query->where($gestor, $reprovado);
            return;
        }

        if ($status === 'pendente_extra') {
            if (!$this->temAprovacaoExtraAtivaParaFiltro($tipoProcessoExtra)) {
                $query->whereRaw('1 = 0');
                return;
            }
            $this->whereApprovalFlowEmAndamento($query, $gestor, $extra, $rh, $reprovado);
            $query->where($gestor, $aprovado);
            $this->whereApprovalFlowCampoVazio($query, $extra);
            return;
        }

        if ($status === 'aprovado_extra') {
            $query->where($extra, $aprovado);
            return;
        }

        if ($status === 'reprovado_extra') {
            $query->where($extra, $reprovado);
            return;
        }

        if ($status === 'pendente_rh') {
            $this->whereApprovalFlowEmAndamento($query, $gestor, $extra, $rh, $reprovado);
            if ($this->temAprovacaoExtraAtivaParaFiltro($tipoProcessoExtra)) {
                $query->where($extra, $aprovado);
            } else {
                $query->where($gestor, $aprovado);
            }
            return;
        }

        if ($status === 'aprovado_rh') {
            $query->where($rh, $aprovado);
            return;
        }

        if ($status === 'reprovado_rh') {
            $query->where($rh, $reprovado);
            return;
        }

        if ($status === 'reprovado') {
            $query->where(function ($q) use ($gestor, $extra, $rh, $reprovado) {
                $q->where($gestor, $reprovado)
                    ->orWhere($extra, $reprovado)
                    ->orWhere($rh, $reprovado);
            });
        }
    }

    /**
     * @param  EloquentBuilder|QueryBuilder  $query
     */
    protected function whereApprovalFlowEmAndamento(
        EloquentBuilder|QueryBuilder $query,
        string $gestor,
        string $extra,
        string $rh,
        string $reprovado
    ): void {
        $this->whereApprovalFlowCampoVazio($query, $rh);
        $this->whereApprovalFlowNaoReprovado($query, $gestor, $reprovado);
        $this->whereApprovalFlowNaoReprovado($query, $extra, $reprovado);
        $this->whereApprovalFlowNaoReprovado($query, $rh, $reprovado);
    }

    /**
     * @param  EloquentBuilder|QueryBuilder  $query
     */
    protected function whereApprovalFlowCampoVazio(EloquentBuilder|QueryBuilder $query, string $coluna): void
    {
        $query->where(function ($q) use ($coluna) {
            $q->whereNull($coluna)->orWhere($coluna, '');
        });
    }

    /**
     * @param  EloquentBuilder|QueryBuilder  $query
     */
    protected function whereApprovalFlowNaoReprovado(
        EloquentBuilder|QueryBuilder $query,
        string $coluna,
        string $reprovado
    ): void {
        $query->where(function ($q) use ($coluna, $reprovado) {
            $q->whereNull($coluna)->orWhere($coluna, '!=', $reprovado);
        });
    }

    /**
     * Aprovado no gestor ou na extra não é o status atual: a lista mostra a etapa seguinte.
     *
     * @param  EloquentBuilder|QueryBuilder  $query
     */
    protected function bloquearStatusAprovacaoIntermediaria(EloquentBuilder|QueryBuilder $query, ?string $status): bool
    {
        if (!in_array($status, ['aprovado_gestor', 'aprovado_extra'], true)) {
            return false;
        }

        $query->whereRaw('1 = 0');

        return true;
    }

    protected function temAprovacaoExtraAtivaParaFiltro(string $tipoProcesso): bool
    {
        $empresaId = (int) ($this->user->empresa_id ?? 0);
        if ($empresaId <= 0 || $tipoProcesso === '') {
            return false;
        }

        try {
            return (bool) AprovacaoExtraConfig::getConfigAtiva($empresaId, $tipoProcesso);
        } catch (\Throwable) {
            return false;
        }
    }
}
