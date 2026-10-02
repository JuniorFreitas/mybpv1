<?php

namespace App\Services\ValorExtraPrevista;

use App\Models\User;
use App\Models\ValorExtraPrevista;
use App\Services\Concerns\AppliesApprovalFlowStatusFilter;
use App\Services\DemissaoPrevista\DemissaoPrevistaFilterApplier;
use Illuminate\Database\Eloquent\Builder;
use MasterTag\DataHora;

class ValorExtraPrevistaFilterApplier
{
    use AppliesApprovalFlowStatusFilter;

    private array $filtros;
    private User $user;

    public function __construct(array $filtros, User $user)
    {
        $this->filtros = $filtros;
        $this->user = $user;
    }

    public function apply(Builder $query): void
    {
        $this->applyToken($query);
        $this->applyPeriodo($query);
        $this->applyCampoBusca($query);
        $this->applyCampoCpf($query);
        $this->applyCampoStatusAprovacao($query);
        DemissaoPrevistaFilterApplier::applyCnpjCentroCusto(
            $query,
            $this->filtros['campoCnpj'] ?? null,
            $this->filtros['campoCentroCusto'] ?? null,
            'valor_extra_previstas',
            $this->user->empresa_id
        );
        $this->applyPermissoes($query);
        $this->applyOrdenacao($query);
    }

    /**
     * Filtro por token (mascara o id na URL/request). Formato: hash . 'lpve' . id
     */
    private function applyToken(Builder $query): void
    {
        $token = $this->filtros['token'] ?? null;
        if ($token === null || $token === '') {
            return;
        }
        $token = (string) $token;
        if (strpos($token, 'lpve') === false) {
            return;
        }
        $parts = explode('lpve', $token, 2);
        $id = isset($parts[1]) ? (int) $parts[1] : 0;
        if ($id > 0) {
            $query->where('id', $id);
        }
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
            $query->where('valor_extra_previstas.created_at', '>=', $inicio->dataHoraInsert())
                ->where('valor_extra_previstas.created_at', '<=', $fim->dataHoraInsert());
            return;
        }
        if (!empty($this->filtros['periodo'])) {
            $periodo = explode(' até ', $this->filtros['periodo']);
            if (count($periodo) === 2) {
                $inicio = new DataHora(trim($periodo[0]) . ' 00:00:00');
                $fim = new DataHora(trim($periodo[1]) . ' 23:59:59');
                $query->where('valor_extra_previstas.created_at', '>=', $inicio->dataHoraInsert())
                    ->where('valor_extra_previstas.created_at', '<=', $fim->dataHoraInsert());
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
                $c->where('nome', 'like', '%' . $busca . '%')->orWhere('id', $busca);
            })->orWhere('valor_extra_previstas.id', $busca);
        });
    }

    private function applyCampoCpf(Builder $query): void
    {
        if (empty($this->filtros['campoCPF'] ?? '')) {
            return;
        }
        $cpfDigits = preg_replace('/\D/', '', (string) $this->filtros['campoCPF']);
        if ($cpfDigits === '') {
            return;
        }
        $query->whereHas('Colaborador.Curriculo', function ($c) use ($cpfDigits) {
            $c->where('cpf', 'like', '%' . $cpfDigits . '%')
                ->orWhereRaw(
                    "REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(cpf,''), '.', ''), '-', ''), '/', ''), ' ', '') LIKE ?",
                    ['%' . $cpfDigits . '%']
                );
        });
    }

    private function applyCampoStatusAprovacao(Builder $query): void
    {
        $status = isset($this->filtros['campoStatusAprovacao']) ? (string) $this->filtros['campoStatusAprovacao'] : null;
        if ($this->bloquearStatusAprovacaoIntermediaria($query, $status)) {
            return;
        }

        $this->applyApprovalFlowStatusFilter(
            $query,
            $status,
            [
                'gestor' => 'valor_extra_previstas.status_aprovacao',
                'extra' => 'valor_extra_previstas.status_aprovacao_extra',
                'rh' => 'valor_extra_previstas.status_aprovacao_rh',
            ],
            ValorExtraPrevista::STATUS_APROVADO,
            ValorExtraPrevista::STATUS_REPROVADO,
            'valor_extra'
        );
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
            $q->where('valor_extra_previstas.user_id', $this->user->id)
                ->orWhere('valor_extra_previstas.gestor_id', $this->user->id);
        });
    }

    private function applyOrdenacao(Builder $query): void
    {
        $ordenacao = $this->filtros['ordenacao'] ?? 'created_at_desc';
        switch ($ordenacao) {
            case 'created_at_asc':
                $query->orderBy('valor_extra_previstas.created_at', 'asc');
                break;
            case 'updated_at_desc':
                $query->orderByDesc('valor_extra_previstas.updated_at');
                break;
            default:
                $query->orderByDesc('valor_extra_previstas.created_at');
        }
    }
}
