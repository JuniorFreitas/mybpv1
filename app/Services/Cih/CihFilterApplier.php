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
        if (!isset($this->filtros['campoBusca']) || empty($this->filtros['campoBusca'])) {
            return;
        }

        $query->whereHas('Colaboradores.Curriculo', function ($q) {
            $q->where('nome', 'like', '%' . $this->filtros['campoBusca'] . '%');
        });
    }

    private function applyStatusFilter(Builder $query): void
    {
        if (!isset($this->filtros['campoStatus']) || empty($this->filtros['campoStatus'])) {
            return;
        }

        $status = $this->filtros['campoStatus'];

        $query->when($status === 'aberto', fn($q) => $q->where('status', 'aberto'))
            ->when($status === 'aprovado_gestor', fn($q) => $q->where('status', 'aprovado')->whereNull('resposta_rh'))
            ->when($status === 'aprovado_rh', fn($q) => $q->where('resposta_rh', 'aprovado'))
            ->when($status === 'reprovado', fn($q) => $q->where(fn($subQ) => $subQ->where('status', 'reprovado')->orWhere('resposta_rh', 'reprovado')));
    }

    private function applyTagFilter(Builder $query): void
    {
        if (!isset($this->filtros['campoTags']) || empty($this->filtros['campoTags'])) {
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
