<?php

namespace App\Services\MudaCargoPrevista;

use App\Models\DemissaoPrevista;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use MasterTag\DataHora;

/**
 * Aplica o filtro da listagem de Mudança de Cargo Prevista (legado).
 */
class MudaCargoPrevistaFilterApplier
{
    private const TABLE = 'muda_cargo_previstas';

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
        $this->applyCnpjCentroCusto($query);
        $this->applyPermissoes($query);
        $this->applyOrdenacao($query);
    }

    private function applyPeriodo(Builder $query): void
    {
        $filtroPeriodo = ($this->filtros['filtroPeriodo'] ?? '') === 'true'
            || ($this->filtros['filtroPeriodo'] ?? false) === true;
        if (!$filtroPeriodo) {
            return;
        }

        $dataInicio = $this->filtros['dataInicio'] ?? null;
        $dataFim = $this->filtros['dataFim'] ?? null;

        if ($dataInicio && $dataFim) {
            $inicio = new DataHora($dataInicio . ' 00:00:00');
            $fim = new DataHora($dataFim . ' 23:59:59');
            $query->where(self::TABLE . '.created_at', '>=', $inicio->dataHoraInsert())
                ->where(self::TABLE . '.created_at', '<=', $fim->dataHoraInsert());
            return;
        }

        if (!empty($this->filtros['periodo'])) {
            $periodo = explode(' até ', $this->filtros['periodo']);
            if (count($periodo) === 2) {
                $inicio = new DataHora(trim($periodo[0]) . ' 00:00:00');
                $fim = new DataHora(trim($periodo[1]) . ' 23:59:59');
                $query->where(self::TABLE . '.created_at', '>=', $inicio->dataHoraInsert())
                    ->where(self::TABLE . '.created_at', '<=', $fim->dataHoraInsert());
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
                ->orWhere(self::TABLE . '.id', $busca);
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
            $query->whereNull(self::TABLE . '.status_aprovacao');
            return;
        }
        if ($status === 'aprovado_gestor') {
            $query->where(self::TABLE . '.status_aprovacao', DemissaoPrevista::STATUS_APROVADO)
                ->whereNull(self::TABLE . '.status_aprovacao_extra');
            return;
        }
        if ($status === 'aprovado_extra') {
            $query->where(self::TABLE . '.status_aprovacao_extra', DemissaoPrevista::STATUS_APROVADO);
            return;
        }
        if ($status === 'aprovado_rh') {
            $query->where(self::TABLE . '.status_aprovacao', DemissaoPrevista::STATUS_APROVADO)
                ->where(self::TABLE . '.status_aprovacao_extra', DemissaoPrevista::STATUS_APROVADO);
            return;
        }
        if ($status === 'reprovado') {
            $query->where(function ($q) {
                $q->where(self::TABLE . '.status_aprovacao', DemissaoPrevista::STATUS_REPROVADO)
                    ->orWhere(self::TABLE . '.status_aprovacao_extra', DemissaoPrevista::STATUS_REPROVADO);
            });
        }
    }

    /**
     * Tabela possui apenas centro_custo_id (sem filial): filtra por IDs de centro de custo.
     */
    private function applyCnpjCentroCusto(Builder $query): void
    {
        $campoCnpj = $this->filtros['campoCnpj'] ?? null;
        $campoCentroCusto = $this->filtros['campoCentroCusto'] ?? null;
        $temCnpj = $campoCnpj !== null && $campoCnpj !== '';
        $temCentro = $campoCentroCusto !== null && $campoCentroCusto !== '' && $campoCentroCusto !== 'todos';

        if (!$temCnpj && !$temCentro) {
            return;
        }

        if ($temCentro && !$temCnpj) {
            $query->whereIn(self::TABLE . '.centro_custo_id', [(int) $campoCentroCusto]);
            return;
        }

        $empresaId = $this->user->empresa_id;
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
        if ($temCentro) {
            $query->where(self::TABLE . '.centro_custo_id', (int) $campoCentroCusto);
            return;
        }

        $ids = $cc->pluck('id')->filter()->map(fn ($id) => (int) $id)->values()->all();
        if ($ids === []) {
            $query->whereRaw('1 = 0');
            return;
        }
        $query->whereIn(self::TABLE . '.centro_custo_id', $ids);
    }

    private function applyPermissoes(Builder $query): void
    {
        if (!empty($this->filtros['_full_export_access'])) {
            return;
        }
        if ($this->user->temPrivilegioGestaoRh()
            || $this->user->can('privilegio_aprovar_por_rh')
            || $this->user->can('privilegio_aprovar_rh')) {
            return;
        }
        $query->where(function ($q) {
            $q->where(self::TABLE . '.user_id', $this->user->id)
                ->orWhere(self::TABLE . '.gestor_id', $this->user->id);
        });
    }

    private function applyOrdenacao(Builder $query): void
    {
        $ordenacao = $this->filtros['ordenacao'] ?? 'created_at_desc';
        switch ($ordenacao) {
            case 'created_at_asc':
                $query->orderBy(self::TABLE . '.created_at', 'asc');
                break;
            case 'updated_at_desc':
                $query->orderByDesc(self::TABLE . '.updated_at');
                break;
            case 'created_at_desc':
            default:
                $query->orderByDesc(self::TABLE . '.created_at');
                break;
        }
    }
}
