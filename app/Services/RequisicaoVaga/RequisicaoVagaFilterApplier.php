<?php

namespace App\Services\RequisicaoVaga;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use MasterTag\DataHora;

/**
 * Aplica o filtro inteiro da Requisição de Vaga (mesmo padrão CIH).
 * Usado na listagem (controller) e na exportação (job) para garantir o mesmo resultado.
 */
class RequisicaoVagaFilterApplier
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
        $this->applyCampoStatus($query);
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
            $query->where('created_at', '>=', $inicio->dataHoraInsert())
                ->where('created_at', '<=', $fim->dataHoraInsert());
            return;
        }

        if (!empty($this->filtros['periodo'])) {
            $periodo = explode(' até ', $this->filtros['periodo']);
            if (count($periodo) === 2) {
                $inicio = new DataHora(trim($periodo[0]) . ' 00:00:00');
                $fim = new DataHora(trim($periodo[1]) . ' 23:59:59');
                $query->where('created_at', '>=', $inicio->dataHoraInsert())
                    ->where('created_at', '<=', $fim->dataHoraInsert());
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
            $q->whereHas('Cargo', fn($c) => $c->where('nome', 'like', '%' . $busca . '%'))
                ->orWhere('id', $busca);
        });
    }

    private function applyCampoStatus(Builder $query): void
    {
        if (!isset($this->filtros['campoStatus']) || $this->filtros['campoStatus'] === '') {
            return;
        }
        $status = $this->filtros['campoStatus'] === 'aberto' ? null : $this->filtros['campoStatus'];
        $query->where('status_aprovacao', $status);
    }

    private function applyPermissoes(Builder $query): void
    {
        // Na exportação o controller repassa _full_export_access (can() no job pode não resolver igual à request)
        if (!empty($this->filtros['_full_export_access'])) {
            return;
        }
        if ($this->user->can('privilegio_gestao_rh') || $this->user->can('privilegio_aprovar_por_rh') || $this->user->can('privilegio_aprovar_rh')) {
            return;
        }
        $query->where(function ($q) {
            $q->where('user_id', $this->user->id)->orWhere('gestor_id', $this->user->id);
        });
    }

    private function applyOrdenacao(Builder $query): void
    {
        $ordenacao = $this->filtros['ordenacao'] ?? 'created_at_desc';
        switch ($ordenacao) {
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'updated_at_desc':
                $query->orderByDesc('updated_at');
                break;
            case 'created_at_desc':
            default:
                $query->orderByDesc('created_at');
        }
    }
}
