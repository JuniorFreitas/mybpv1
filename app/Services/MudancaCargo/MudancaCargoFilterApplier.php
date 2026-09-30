<?php

namespace App\Services\MudancaCargo;

use App\Models\MudancaCargo;
use App\Models\User;
use App\Services\Concerns\AppliesApprovalFlowStatusFilter;
use App\Services\DemissaoPrevista\DemissaoPrevistaFilterApplier;
use Illuminate\Database\Eloquent\Builder;
use MasterTag\DataHora;

class MudancaCargoFilterApplier
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
        $this->applyCnpjCentroCusto($query);
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
            $q->whereHas('Admissao.Feedback.Curriculo', function ($c) use ($busca) {
                $c->where('nome', 'like', '%' . $busca . '%')->orWhere('id', $busca);
            })
                ->orWhereHas('Colaborador', function ($c) use ($busca) {
                    $c->where('nome', 'like', '%' . $busca . '%')->orWhere('id', $busca);
                })
                ->orWhere('mudanca_cargo.id', $busca);
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
        $query->whereHas('Admissao.Feedback.Curriculo', function ($c) use ($cpfDigits) {
            $c->where('cpf', 'like', '%' . $cpfDigits . '%')
                ->orWhereRaw(
                    "REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(cpf,''), '.', ''), '-', ''), '/', ''), ' ', '') LIKE ?",
                    ['%' . $cpfDigits . '%']
                );
        });
    }

    private function applyCnpjCentroCusto(Builder $query): void
    {
        $campoCnpj = $this->filtros['campoCnpj'] ?? null;
        $campoCentroCusto = $this->filtros['campoCentroCusto'] ?? null;
        $temCnpj = $campoCnpj !== null && $campoCnpj !== '';
        $temCentro = $campoCentroCusto !== null && $campoCentroCusto !== '' && $campoCentroCusto !== 'todos';

        if (!$temCnpj && !$temCentro) {
            return;
        }

        $query->where(function ($q) use ($campoCnpj, $campoCentroCusto, $temCnpj, $temCentro) {
            $q->where(function ($sub) use ($campoCnpj, $campoCentroCusto, $temCnpj, $temCentro) {
                $sub->where('mantem_centro_custo', true)
                    ->orWhereNull('novo_centro_custo_id');
                $sub->whereHas('Admissao', function ($adm) use ($campoCnpj, $campoCentroCusto) {
                    DemissaoPrevistaFilterApplier::applyCnpjCentroCusto(
                        $adm,
                        $campoCnpj,
                        $campoCentroCusto,
                        'admissoes',
                        $this->user->empresa_id
                    );
                });
            })->orWhere(function ($sub) use ($campoCnpj, $campoCentroCusto, $temCentro) {
                $sub->where('mantem_centro_custo', false)
                    ->whereNotNull('novo_centro_custo_id');
                if ($temCentro) {
                    $id = (int) $campoCentroCusto;
                    $sub->where(function ($inner) use ($id) {
                        $inner->where('novo_centro_custo_id', $id)
                            ->orWhere('novo_centro_custo_filial_id', $id);
                    });
                    return;
                }
                if ($campoCnpj !== null && $campoCnpj !== '') {
                    $centros = (new \App\Models\CentroCusto())->listaCentroCustoPorCnpj($this->user->empresa_id);
                    if ($centros instanceof \Illuminate\Http\JsonResponse) {
                        $sub->whereRaw('1 = 0');
                        return;
                    }
                    $cnpjKey = preg_replace('/[^0-9]/', '', (string) $campoCnpj);
                    $cc = $centros['centros_custos'][$campoCnpj]
                        ?? $centros['centros_custos'][$cnpjKey]
                        ?? null;
                    if (!$cc) {
                        $sub->whereRaw('1 = 0');
                        return;
                    }
                    $ccIds = collect($cc)->pluck('id')->filter()->map(fn ($v) => (int) $v)->values()->all();
                    $filialIds = collect($cc)->pluck('filial_id')->filter()->map(fn ($v) => (int) $v)->values()->all();
                    $sub->where(function ($inner) use ($ccIds, $filialIds) {
                        if ($ccIds !== []) {
                            $inner->whereIn('novo_centro_custo_id', $ccIds);
                        }
                        if ($filialIds !== []) {
                            $inner->orWhereIn('novo_centro_custo_filial_id', $filialIds);
                        }
                    });
                }
            });
        });
    }

    private function applyCampoStatusAprovacao(Builder $query): void
    {
        $this->applyApprovalFlowStatusFilter(
            $query,
            isset($this->filtros['campoStatusAprovacao']) ? (string) $this->filtros['campoStatusAprovacao'] : null,
            [
                'gestor' => 'status_aprovacao_gestor',
                'extra' => 'status_aprovacao_extra',
                'rh' => 'status_aprovacao_rh',
            ],
            MudancaCargo::STATUS_APROVADO,
            MudancaCargo::STATUS_REPROVADO,
            'mudanca_cargo'
        );
    }

    private function applyPermissoes(Builder $query): void
    {
        if (!empty($this->filtros['_full_export_access'])) {
            return;
        }
        if ($this->user->can('privilegio_gestao_rh') || $this->user->can('privilegio_aprovar_por_rh') || $this->user->can('privilegio_aprovar_rh')) {
            return;
        }
        $query->where(function ($q) {
            $q->where('solicitante_id', $this->user->id)->orWhere('gestor_id', $this->user->id);
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
            default:
                $query->orderByDesc('created_at');
        }
    }
}
