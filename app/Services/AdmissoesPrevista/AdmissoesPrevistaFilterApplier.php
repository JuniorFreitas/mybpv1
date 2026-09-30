<?php

namespace App\Services\AdmissoesPrevista;

use App\Models\AdmissoesPrevista;
use App\Models\User;
use App\Services\Concerns\AppliesApprovalFlowStatusFilter;
use App\Services\DemissaoPrevista\DemissaoPrevistaFilterApplier;
use Illuminate\Database\Eloquent\Builder;
use MasterTag\DataHora;

/**
 * Aplica o filtro da listagem de Admissão Prevista.
 */
class AdmissoesPrevistaFilterApplier
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
        $this->applyTipoContrato($query);
        DemissaoPrevistaFilterApplier::applyCnpjCentroCusto(
            $query,
            $this->filtros['campoCnpj'] ?? null,
            $this->filtros['campoCentroCusto'] ?? null,
            'admissoes_previstas',
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
            $q->where('admissoes_previstas.nome_pessoa', 'like', '%' . $busca . '%')
                ->orWhere('admissoes_previstas.id', $busca)
                ->orWhereHas('Colaborador', function ($c) use ($busca) {
                    $c->where('nome', 'like', '%' . $busca . '%')
                        ->orWhere('id', $busca);
                });
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
        $query->whereHas('Colaborador', function ($colaborador) use ($cpfDigits) {
            $colaborador->whereHas('Curriculo', function ($curriculo) use ($cpfDigits) {
                $curriculo->where('cpf', 'like', '%' . $cpfDigits . '%')
                    ->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(cpf,''), '.', ''), '-', ''), '/', ''), ' ', '') LIKE ?",
                        ['%' . $cpfDigits . '%']
                    );
            });
        });
    }

    private function applyCampoStatusAprovacao(Builder $query): void
    {
        $this->applyApprovalFlowStatusFilter(
            $query,
            isset($this->filtros['campoStatusAprovacao']) ? (string) $this->filtros['campoStatusAprovacao'] : null,
            [
                'gestor' => 'admissoes_previstas.status_aprovacao',
                'extra' => 'admissoes_previstas.status_aprovacao_extra',
                'rh' => 'admissoes_previstas.status_aprovacao_rh',
            ],
            AdmissoesPrevista::STATUS_APROVADO,
            AdmissoesPrevista::STATUS_REPROVADO,
            'admissao'
        );
    }

    private function applyTipoContrato(Builder $query): void
    {
        $tipo = $this->filtros['tipo_contrato'] ?? $this->filtros['campoTipoAdmissao'] ?? null;
        if ($tipo === null || $tipo === '') {
            return;
        }
        $query->where('tipo_contrato', $tipo);
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
            default:
                $query->orderByDesc('created_at');
        }
    }
}
