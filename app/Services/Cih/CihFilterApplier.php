<?php

namespace App\Services\Cih;

use Illuminate\Database\Eloquent\Builder;
use MasterTag\DataHora;

class CihFilterApplier
{
    private array $filtros;

    public function __construct(array $filtros)
    {
        $this->filtros = $filtros;
    }

    public function apply(Builder $query): void
    {
        $this->applyPeriodFilter($query);
        $this->applySearchFilter($query);
        $this->applyStatusFilter($query);
        $this->applyTagFilter($query);
        $this->applyAreaFilter($query);
        $this->applyCnpjFilter($query);
        $this->applyCostCenterFilter($query);
        $this->applyManagerFilter($query);
    }

    private function applyPeriodFilter(Builder $query): void
    {
        if (!($this->filtros['filtroPeriodo'] ?? false) || !isset($this->filtros['periodo'])) {
            return;
        }

        $periodo = explode(' até ', $this->filtros['periodo']);
        if (count($periodo) !== 2) {
            return;
        }

        $dataInicio = new DataHora($periodo[0] . ' 00:00:00');
        $dataFim = new DataHora($periodo[1] . ' 23:59:59');

        $query->whereBetween('data_lancamento', [
            $dataInicio->dataHoraInsert(),
            $dataFim->dataHoraInsert()
        ]);
    }

    private function applySearchFilter(Builder $query): void
    {
        if (empty($this->filtros['campoBusca'] ?? '')) {
            return;
        }

        $busca = trim((string) $this->filtros['campoBusca']);
        if ($busca === '') {
            return;
        }

        $query->where(function (Builder $q) use ($busca) {
            $q->whereHas('Colaboradores.Curriculo', function ($c) use ($busca) {
                $c->where('nome', 'like', '%' . $busca . '%');
            })->orWhere('id', $busca);
        });
    }

    private function applyStatusFilter(Builder $query): void
    {
        $status = $this->filtros['campoStatusAprovacao']
            ?? $this->filtros['campoStatus']
            ?? null;

        if ($status === null || $status === '') {
            return;
        }

        $status = (string) $status;

        // CIH: status (gestor: aberto|aprovado|reprovado) + resposta_rh (null|aprovado|reprovado). Sem etapa extra.
        if (in_array($status, ['pendente_extra', 'aprovado_extra', 'reprovado_extra'], true)) {
            $query->whereRaw('1 = 0');
            return;
        }

        if ($status === 'aberto') {
            $query->where('status', '!=', 'reprovado')
                ->where(function (Builder $q) {
                    $q->whereNull('resposta_rh')->orWhere('resposta_rh', '');
                });
            return;
        }

        if ($status === 'pendente_gestor') {
            $query->where('status', 'aberto')
                ->where(function (Builder $q) {
                    $q->whereNull('resposta_rh')->orWhere('resposta_rh', '');
                });
            return;
        }

        if ($status === 'aprovado_gestor') {
            $query->where('status', 'aprovado');
            return;
        }

        if ($status === 'reprovado_gestor') {
            $query->where('status', 'reprovado');
            return;
        }

        if ($status === 'pendente_rh') {
            $query->where('status', 'aprovado')
                ->where(function (Builder $q) {
                    $q->whereNull('resposta_rh')->orWhere('resposta_rh', '');
                });
            return;
        }

        if ($status === 'aprovado_rh') {
            $query->where('resposta_rh', 'aprovado');
            return;
        }

        if ($status === 'reprovado_rh') {
            $query->where('resposta_rh', 'reprovado');
            return;
        }

        if ($status === 'reprovado') {
            $query->where(function (Builder $q) {
                $q->where('status', 'reprovado')->orWhere('resposta_rh', 'reprovado');
            });
        }
    }

    private function applyTagFilter(Builder $query): void
    {
        if (!isset($this->filtros['campoTags']) || $this->filtros['campoTags'] === '' || $this->filtros['campoTags'] === null) {
            return;
        }

        // 0 / "0" = tipo "Outro" (lançamentos sem tag_id)
        if ((string) $this->filtros['campoTags'] === '0') {
            $query->whereNull('tag_id');
            return;
        }

        $query->whereHas('Tag', fn($q) => $q->whereId($this->filtros['campoTags']));
    }

    private function applyAreaFilter(Builder $query): void
    {
        if (!isset($this->filtros['campoAreas']) || empty($this->filtros['campoAreas'])) {
            return;
        }

        $query->whereHas('Area', fn($q) => $q->whereId($this->filtros['campoAreas']));
    }

    private function applyCnpjFilter(Builder $query): void
    {
        if (empty($this->filtros['campoCnpj'] ?? '')) {
            return;
        }

        // CC específico já restringe; CNPJ só amplia o conjunto quando CC não veio.
        if (!empty($this->filtros['campoCentrosDeCusto'] ?? '')) {
            return;
        }

        $empresaId = auth()->user()?->empresa_id;
        if (!$empresaId) {
            return;
        }

        $lista = (new \App\Models\CentroCusto())->listaCentroCustoPorCnpj($empresaId);
        $grupo = collect($lista['centros_custos'][$this->filtros['campoCnpj']] ?? []);
        $ids = $grupo->pluck('id')->filter()->values()->all();

        if ($ids === []) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->whereIn('centro_custo_id', $ids);
    }

    private function applyCostCenterFilter(Builder $query): void
    {
        if (!isset($this->filtros['campoCentrosDeCusto']) || empty($this->filtros['campoCentrosDeCusto'])) {
            return;
        }

        $query->whereHas('CentroDeCusto', fn($q) => $q->whereId($this->filtros['campoCentrosDeCusto'])
        );
    }

    private function applyManagerFilter(Builder $query): void
    {
        if (!isset($this->filtros['campoGestores']) || empty($this->filtros['campoGestores'])) {
            return;
        }

        $query->whereHas('GestorAprovacao', fn($q) => $q->whereId($this->filtros['campoGestores'])
        );
    }
}
