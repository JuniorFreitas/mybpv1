<?php

namespace App\Services\Cih;

use App\Models\CentroCusto;
use App\Models\Cih;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CihQueryBuilder
{
    private User $user;
    private array $filtros;
    private bool $isExport;

    public function __construct(User $user, array $filtros = [], bool $isExport = false)
    {
        $this->user = $user;
        $this->filtros = $filtros;
        $this->isExport = $isExport;
    }

    public function build(): Builder
    {
        $query = $this->getBaseQueryWithPermissions();

        $this->applyFilters($query);

        return $query->orderByDesc('created_at');
    }

    private function getBaseQueryWithPermissions(): Builder
    {
        $baseRelationships = $this->getBaseRelationships();

        // Lógica de permissões igual ao controller original
        if ($this->user->can('admissao_cih_privilegio_adm')) {
            $query = Cih::with($baseRelationships);
        } elseif ($this->user->grupo_id == 113) { // POG para Montisol
            $query = $this->buildMontisQuery($baseRelationships);
        } else {
            $query = Cih::vinculados()->with($baseRelationships);
        }

        // Para export, adicionar relacionamentos específicos do export
        if ($this->isExport) {
            $query->with($this->getExportSpecificRelationships());
        } else {
            // Para listagem, adicionar relacionamentos específicos da listagem
            $query->with($this->getListingSpecificRelationships());
        }

        return $query;
    }

    private function getBaseRelationships(): array
    {
        return [
            'Colaboradores.Demissao' => function ($query) {
                $query->select('id', 'feedback_id', 'data_desmobilizacao',
                    DB::raw('DATEDIFF(NOW(), data_desmobilizacao) AS dias'));
            },
            'Tag:id,label',
            'Area',
            'CentroDeCusto',
            'ResponsavelLancamento:id,nome',
            'ResponsavelAprovacao:id,nome',
            'RhAprovacao:id,nome'
        ];
    }

    private function getExportSpecificRelationships(): array
    {
        return [
            'Colaboradores.Curriculo',
            'Colaboradores.Admissao',
            'Colaboradores.VagaAberta.Vaga',
        ];
    }

    private function getListingSpecificRelationships(): array
    {
        return [
            'Colaboradores.Admissao:id,feedback_id,data_admissao,pis,centro_custo_id',
            'Colaboradores.Admissao.CentroCusto:id,label'
        ];
    }

    private function buildMontisQuery(array $baseRelationships): Builder
    {
        $cc = (new CentroCusto())->listaCentroCustoPorCnpj($this->user->empresa_id);
        $ccMatriz = collect($cc['centros_custos']['12557849000140'])->where('ativo', '=', true);

        return Cih::with($baseRelationships)
            ->whereHas('CentroDeCusto', function ($query) use ($ccMatriz) {
                $query->whereIn('id', $ccMatriz->pluck('id')->toArray());
            });
    }

    private function applyFilters(Builder $query): void
    {
        $filterApplier = new CihFilterApplier($this->filtros);
        $filterApplier->apply($query);
    }

    // Método estático para facilitar o uso no controller
    public static function forListing(User $user, array $filtros = []): Builder
    {
        return (new self($user, $filtros, false))->build();
    }

    // Método estático para facilitar o uso no export
    public static function forExport(User $user, array $filtros = []): Builder
    {
        return (new self($user, $filtros, true))->build();
    }
}
