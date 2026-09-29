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
        $this->applyCampoCpf($query);
        $this->applyCampoStatusAprovacao($query);
        self::applyCnpjCentroCusto(
            $query,
            $this->filtros['campoCnpj'] ?? null,
            $this->filtros['campoCentroCusto'] ?? null,
            'demissao_previstas',
            $this->user->empresa_id
        );
        $this->applyPermissoes($query);
        $this->applyOrdenacao($query);
    }

    /**
     * Filtro por Lotação (CNPJ) e/ou Centro de Custo — usado na listagem e na exportação.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  string|null  $campoCnpj
     * @param  string|int|null  $campoCentroCusto
     */
    public static function applyCnpjCentroCusto($query, $campoCnpj, $campoCentroCusto, string $alias = 'demissao_previstas', $empresaId = null): void
    {
        $temCnpj = $campoCnpj !== null && $campoCnpj !== '';
        $temCentro = $campoCentroCusto !== null && $campoCentroCusto !== '' && $campoCentroCusto !== 'todos';

        if (!$temCnpj && !$temCentro) {
            return;
        }

        $colCc = "{$alias}.centro_custo_id";
        $colFilialCc = "{$alias}.centro_custo_filial_id";
        $colFilial = "{$alias}.filial";

        if ($temCnpj) {
            $empresaId = $empresaId ?: (auth()->user()->empresa_id ?? null);
            $centros_custos = (new \App\Models\CentroCusto())->listaCentroCustoPorCnpj($empresaId);

            if ($centros_custos instanceof \Illuminate\Http\JsonResponse) {
                $query->whereRaw('1 = 0');
                return;
            }

            $cnpjKey = preg_replace('/[^0-9]/', '', (string) $campoCnpj);
            $cc = $centros_custos['centros_custos'][$campoCnpj]
                ?? $centros_custos['centros_custos'][$cnpjKey]
                ?? null;

            if (!$cc || !isset($cc[0])) {
                $query->whereRaw('1 = 0');
                return;
            }

            $cc = collect($cc);

            if (!$temCentro) {
                if ($cc[0]['matriz']) {
                    $query->where(function ($q) use ($cc, $colCc) {
                        $q->whereIn($colCc, $cc->pluck('id')->toArray())
                            ->orWhereNull($colCc);
                    })->where($colFilial, false);
                } else {
                    $query->where(function ($q) use ($cc, $colFilialCc) {
                        $q->whereIn($colFilialCc, $cc->pluck('filial_id')->filter()->values()->toArray())
                            ->orWhereNull($colFilialCc);
                    })->where($colFilial, true);
                }
                return;
            }

            $ids = [(string) $campoCentroCusto];
            if ($cc[0]['matriz']) {
                $query->whereIn($colCc, $ids)->where($colFilial, false);
            } else {
                $query->where(function ($q) use ($ids, $colCc, $colFilialCc) {
                    $q->whereIn($colCc, $ids)->orWhereIn($colFilialCc, $ids);
                })->where($colFilial, true);
            }
            return;
        }

        $ids = [(string) $campoCentroCusto];
        $query->where(function ($q) use ($ids, $colCc, $colFilialCc) {
            $q->whereIn($colCc, $ids)->orWhereIn($colFilialCc, $ids);
        });
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
