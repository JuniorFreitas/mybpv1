<?php

namespace App\Services\TransferenciaPrevista;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use MasterTag\DataHora;

class TransferenciaPrevistaFilterApplier
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
        $this->applyCampoBusca($query);
        $this->applyCampoStatus($query);
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
            $q->whereHas('Colaborador', function ($c) use ($busca) {
                $c->where('nome', 'like', '%' . $busca . '%')->orWhere('id', $busca);
            })->orWhere('id', $busca);
        });
    }

    private function applyCampoStatus(Builder $query): void
    {
        if (!isset($this->filtros['campoStatus']) || $this->filtros['campoStatus'] === '') {
            return;
        }

        $status = $this->filtros['campoStatus'];

        // Em aberto: ainda em andamento (sem RH final) e sem reprovação em nenhuma etapa.
        // Não basta status_aprovacao null — origem dispensada/omitida deixa null e a
        // reprova pode estar em destino, gestor único, extra ou RH.
        if ($status === 'aberto') {
            $query->where(function ($q) {
                $q->whereNull('resposta_rh')->orWhere('resposta_rh', '');
            });
            $this->whereNaoReprovado($query, 'status_aprovacao');
            $this->whereNaoReprovado($query, 'status_aprovacao_gestor_destino');
            $this->whereNaoReprovado($query, 'status_aprovacao_gestor_unico');
            $this->whereNaoReprovado($query, 'status_aprovacao_extra');
            return;
        }

        // Aprovado = efetivado pelo RH (badge final da timeline).
        if ($status === 'aprovado') {
            $query->where('resposta_rh', 'aprovado');
            return;
        }

        if ($status === 'reprovado') {
            $query->where(function ($q) {
                $q->where('status_aprovacao', 'reprovado')
                    ->orWhere('status_aprovacao_gestor_destino', 'reprovado')
                    ->orWhere('status_aprovacao_gestor_unico', 'reprovado')
                    ->orWhere('status_aprovacao_extra', 'reprovado')
                    ->orWhere('resposta_rh', 'reprovado');
            });
        }
    }

    private function whereNaoReprovado(Builder $query, string $coluna): void
    {
        $query->where(function ($q) use ($coluna) {
            $q->whereNull($coluna)->orWhere($coluna, '!=', 'reprovado');
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
            $q->where('user_id', $this->user->id)
                ->orWhere('gestor_id', $this->user->id)
                ->orWhere('gestor_destino_id', $this->user->id);
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
