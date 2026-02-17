<?php

namespace App\Services\DemissaoPrevista;

use App\Models\DemissaoPrevista;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use MasterTag\DataHora;

/**
 * Aplica o filtro da listagem de Demissão Prevista.
 * Usado na exportação (job) para garantir o mesmo resultado da tela.
 */
class DemissaoPrevistaFilterApplier
{
    private array $filtros;
    private User $user;

    public function __construct(array $filtros, User $user)
    {
        $this->filtros = $filtros;
        $this->user = $user;
    }

    public function apply(Builder $query): void
    {
        $this->applyPeriodo($query);
        $this->applyCampoBusca($query);
        $this->applyCampoStatusAprovacao($query);
        $this->applyPermissoes($query);
        $this->applyOrdenacao($query);
    }

    private function applyPeriodo(Builder $query): void
    {
        $filtroPeriodo = ($this->filtros['filtroPeriodo'] ?? '') === 'true' || ($this->filtros['filtroPeriodo'] ?? false) === true;
        if (!$filtroPeriodo) {
            return;
        }

        $dataInicio = $this->filtros['dataInicio'] ?? null;
        $dataFim = $this->filtros['dataFim'] ?? null;

        if ($dataInicio && $dataFim) {
            $inicio = new DataHora($dataInicio . ' 00:00:00');
            $fim = new DataHora($dataFim . ' 23:59:59');
            $query->where('demissao_previstas.created_at', '>=', $inicio->dataHoraInsert())
                ->where('demissao_previstas.created_at', '<=', $fim->dataHoraInsert());
            return;
        }

        if (!empty($this->filtros['periodo'])) {
            $periodo = explode(' até ', $this->filtros['periodo']);
            if (count($periodo) === 2) {
                $inicio = new DataHora(trim($periodo[0]) . ' 00:00:00');
                $fim = new DataHora(trim($periodo[1]) . ' 23:59:59');
                $query->where('demissao_previstas.created_at', '>=', $inicio->dataHoraInsert())
                    ->where('demissao_previstas.created_at', '<=', $fim->dataHoraInsert());
            }
        }
    }

    private function applyCampoBusca(Builder $query): void
    {
        if (empty($this->filtros['campoBusca'] ?? '')) {
            return;
        }
        $busca = $this->filtros['campoBusca'];
        $query->where(function ($q) use ($busca) {
            $q->whereHas('Colaborador', function ($c) use ($busca) {
                $c->where('nome', 'like', '%' . $busca . '%')
                    ->orWhere('id', $busca);
            })
                ->orWhere('demissao_previstas.id', $busca);
        });
    }

    private function applyCampoStatusAprovacao(Builder $query): void
    {
        if (!isset($this->filtros['campoStatusAprovacao']) || $this->filtros['campoStatusAprovacao'] === '') {
            return;
        }
        $status = $this->filtros['campoStatusAprovacao'];
        if ($status === 'aberto') {
            $query->whereNull('demissao_previstas.status_aprovacao');
            return;
        }
        if ($status === 'aprovado_gestor') {
            $query->where('demissao_previstas.status_aprovacao', DemissaoPrevista::STATUS_APROVADO)
                ->whereNull('demissao_previstas.status_aprovacao_extra')
                ->whereNull('demissao_previstas.status_aprovacao_rh');
            return;
        }
        if ($status === 'aprovado_extra') {
            $query->where('demissao_previstas.status_aprovacao_extra', DemissaoPrevista::STATUS_APROVADO)
                ->whereNull('demissao_previstas.status_aprovacao_rh');
            return;
        }
        if ($status === 'aprovado_rh') {
            $query->where('demissao_previstas.status_aprovacao_rh', DemissaoPrevista::STATUS_APROVADO);
            return;
        }
        if ($status === 'reprovado') {
            $query->where(function ($q) {
                $q->where('demissao_previstas.status_aprovacao', DemissaoPrevista::STATUS_REPROVADO)
                    ->orWhere('demissao_previstas.status_aprovacao_extra', DemissaoPrevista::STATUS_REPROVADO)
                    ->orWhere('demissao_previstas.status_aprovacao_rh', DemissaoPrevista::STATUS_REPROVADO);
            });
        }
    }

    private function applyPermissoes(Builder $query): void
    {
        if (!empty($this->filtros['_full_export_access'])) {
            return;
        }
        if ($this->user->temPrivilegioGestaoRh() || $this->user->can('privilegio_aprovar_por_rh') || $this->user->can('privilegio_aprovar_rh')) {
            return;
        }
        $query->where(function ($q) {
            $q->where('demissao_previstas.user_id', $this->user->id)
                ->orWhere('demissao_previstas.gestor_id', $this->user->id);
        });
    }

    private function applyOrdenacao(Builder $query): void
    {
        $ordenacao = $this->filtros['ordenacao'] ?? 'created_at_desc';
        switch ($ordenacao) {
            case 'created_at_asc':
                $query->orderBy('demissao_previstas.created_at', 'asc');
                break;
            case 'updated_at_desc':
                $query->orderByDesc('demissao_previstas.updated_at');
                break;
            case 'created_at_desc':
            default:
                $query->orderByDesc('demissao_previstas.created_at');
                break;
        }
    }
}
