<?php

namespace App\Services\FeriasPrevista;

use App\Models\Ferias;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use MasterTag\DataHora;

/**
 * Aplica o filtro da listagem de Férias (Planejamento - Movimentação - Férias).
 */
class FeriasPrevistaFilterApplier
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
        $this->applyToken($query);
        $this->applyPeriodo($query);
        $this->applyFiltroVencimento($query);
        $this->applyFiltroInicioFerias($query);
        $this->applyCampoBusca($query);
        $this->applyCampoStatusAprovacao($query);
        $this->applyPermissoes($query);
        $this->applyPeriodoAquisitivo($query);
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
            $query->where('data_solicitacao', '>=', $inicio->dataHoraInsert())
                ->where('data_solicitacao', '<=', $fim->dataHoraInsert());
            return;
        }
        if (!empty($this->filtros['periodo'])) {
            $periodo = explode(' até ', $this->filtros['periodo']);
            if (count($periodo) === 2) {
                $inicio = new DataHora(trim($periodo[0]) . ' 00:00:00');
                $fim = new DataHora(trim($periodo[1]) . ' 23:59:59');
                $query->where('data_solicitacao', '>=', $inicio->dataHoraInsert())
                    ->where('data_solicitacao', '<=', $fim->dataHoraInsert());
            }
        }
    }

    private function applyFiltroVencimento(Builder $query): void
    {
        if (($this->filtros['filtroVencimento'] ?? '') !== 'true') {
            return;
        }
        $dataInicioVenc = $this->filtros['dataInicioVencimento'] ?? null;
        $dataFimVenc = $this->filtros['dataFimVencimento'] ?? null;
        if ($dataInicioVenc && $dataFimVenc) {
            $inicio = new DataHora($dataInicioVenc . ' 00:00:00');
            $fim = new DataHora($dataFimVenc . ' 23:59:59');
            $query->where('ultima_data', '>=', $inicio->dataHoraInsert())
                ->where('ultima_data', '<=', $fim->dataHoraInsert());
            return;
        }
        if (!empty($this->filtros['vencimento'])) {
            $periodoVenc = explode(' até ', $this->filtros['vencimento']);
            if (count($periodoVenc) === 2) {
                $inicio = new DataHora(trim($periodoVenc[0]) . ' 00:00:00');
                $fim = new DataHora(trim($periodoVenc[1]) . ' 23:59:59');
                $query->where('ultima_data', '>=', $inicio->dataHoraInsert())
                    ->where('ultima_data', '<=', $fim->dataHoraInsert());
            }
        }
    }

    private function applyFiltroInicioFerias(Builder $query): void
    {
        if (($this->filtros['filtroInicioFerias'] ?? '') !== 'true') {
            return;
        }
        $dataInicioFer = $this->filtros['dataInicioFerias'] ?? null;
        $dataFimFer = $this->filtros['dataFimFerias'] ?? null;
        if ($dataInicioFer && $dataFimFer) {
            $inicio = new DataHora($dataInicioFer . ' 00:00:00');
            $fim = new DataHora($dataFimFer . ' 23:59:59');
            $query->where('data_saida', '>=', $inicio->dataHoraInsert())
                ->where('data_saida', '<=', $fim->dataHoraInsert());
            return;
        }
        if (!empty($this->filtros['inicioFerias'])) {
            $periodoFer = explode(' até ', $this->filtros['inicioFerias']);
            if (count($periodoFer) === 2) {
                $inicio = new DataHora(trim($periodoFer[0]) . ' 00:00:00');
                $fim = new DataHora(trim($periodoFer[1]) . ' 23:59:59');
                $query->where('data_saida', '>=', $inicio->dataHoraInsert())
                    ->where('data_saida', '<=', $fim->dataHoraInsert());
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
            })->orWhere('id', $busca);
        });
    }

    private function applyCampoStatusAprovacao(Builder $query): void
    {
        if (!isset($this->filtros['campoStatusAprovacao']) || $this->filtros['campoStatusAprovacao'] === '') {
            return;
        }
        $status = $this->filtros['campoStatusAprovacao'];
        if ($status === 'aberto') {
            $query->whereNull('status_aprovacao_gestor');
            return;
        }
        if ($status === 'aprovado_gestor') {
            $query->where('status_aprovacao_gestor', Ferias::STATUS_APROVADO)->whereNull('status_aprovacao_rh');
            return;
        }
        if ($status === 'aprovado_rh') {
            $query->where('status_aprovacao_rh', Ferias::STATUS_APROVADO);
            return;
        }
        $query->where(function ($q) {
            $q->where('status_aprovacao_gestor', Ferias::STATUS_REPROVADO)
                ->orWhere('status_aprovacao_rh', Ferias::STATUS_REPROVADO);
        });
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

    private function applyPeriodoAquisitivo(Builder $query): void
    {
        if (!empty($this->filtros['filtroPeriodoAquisitivo'])) {
            $query->whereHas('PeriodoAquisitivo', function ($q) {
                $q->where('id', $this->filtros['filtroPeriodoAquisitivo']);
            });
        } else {
            $query->whereHas('PeriodoAquisitivo', function ($q) {
                $q->whereIn('ano_inicial', [date('Y') - 3, date('Y') - 2, date('Y') - 1, (int) date('Y')]);
            });
        }
    }

    private function applyOrdenacao(Builder $query): void
    {
        $ordenacao = $this->filtros['ordenacao'] ?? 'created_at_desc';
        switch ($ordenacao) {
            case 'created_at_asc':
                $query->orderBy('data_solicitacao', 'asc');
                break;
            case 'updated_at_desc':
                $query->orderByDesc('updated_at');
                break;
            default:
                $query->orderByDesc('data_solicitacao');
        }
    }
}
