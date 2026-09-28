<?php

namespace App\Services\Cih;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CihQueryBuilder
{
    private User $user;
    private array $filtros;
    private bool $isExport;
    private CihAcessoService $acessoService;

    public function __construct(User $user, array $filtros = [], bool $isExport = false)
    {
        $this->user = $user;
        $this->filtros = $filtros;
        $this->isExport = $isExport;
        $this->acessoService = new CihAcessoService();
    }

    public function build(): Builder
    {
        $query = $this->getBaseQueryWithPermissions();

        $this->applyFilters($query);

        return $query->orderByDesc('created_at');
    }

    private function getBaseQueryWithPermissions(): Builder
    {
        $query = $this->acessoService->queryBaseComEscopo(
            $this->user,
            $this->getBaseRelationships()
        );

        if ($this->isExport) {
            return $query->with($this->getExportSpecificRelationships());
        }

        return $query->with($this->getListingSpecificRelationships());
    }

    private function getBaseRelationships(): array
    {
        return [
            'Colaboradores.Demissao' => function ($query) {
                $query->select(
                    'id',
                    'feedback_id',
                    'data_desmobilizacao',
                    DB::raw('DATEDIFF(NOW(), data_desmobilizacao) AS dias')
                );
            },
            'Tag:id,label',
            'Area',
            'CentroDeCusto',
            'ResponsavelLancamento:id,nome',
            'ResponsavelAprovacao:id,nome',
            'RhAprovacao:id,nome',
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
            'Colaboradores.Admissao.CentroCusto:id,label',
        ];
    }

    private function applyFilters(Builder $query): void
    {
        (new CihFilterApplier($this->filtros))->apply($query);
    }

    public static function forListing(User $user, array $filtros = []): Builder
    {
        return (new self($user, $filtros, false))->build();
    }

    public static function forExport(User $user, array $filtros = []): Builder
    {
        return (new self($user, $filtros, true))->build();
    }
}
