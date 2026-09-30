<?php

namespace App\Services\Cih;

use App\Models\Cih;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CihQueryBuilder
{
    /** Colunas da listagem CIH (cards) — evita overfetch de obs/campos internos. */
    private const LISTING_COLUMNS = [
        'id',
        'tag_id',
        'outra_tag',
        'area_id',
        'outra_area',
        'centro_custo_id',
        'gestor_id',
        'status',
        'resposta_rh',
        'user_aprovacao_id',
        'user_rh_id',
        'user_lancamento_id',
        'data_lancamento',
        'data_aprovacao',
        'data_aprovacao_rh',
        'acao',
        'varios_colaboradores',
        'created_at',
        'empresa_id',
    ];

    /** Colunas usadas na exportação CSV/Excel. */
    private const EXPORT_COLUMNS = [
        'id',
        'tag_id',
        'outra_tag',
        'area_id',
        'outra_area',
        'centro_custo_id',
        'status',
        'resposta_rh',
        'user_aprovacao_id',
        'user_rh_id',
        'user_lancamento_id',
        'data_lancamento',
        'data_aprovacao',
        'data_aprovacao_rh',
        'acao',
        'created_at',
        'empresa_id',
    ];

    private User $user;
    private array $filtros;
    private bool $isExport;
    private CihAcessoService $acessoService;

    public function __construct(User $user, array $filtros = [], bool $isExport = false)
    {
        $this->user = $user;
        $this->filtros = $filtros;
        $this->isExport = $isExport;
        $this->acessoService = app(CihAcessoService::class);
    }

    public function build(): Builder
    {
        $query = $this->getBaseQueryWithPermissions();

        $this->applyFilters($query);

        $query->select($this->isExport ? self::EXPORT_COLUMNS : self::LISTING_COLUMNS);

        return $query->orderByDesc('created_at');
    }

    private function getBaseQueryWithPermissions(): Builder
    {
        // Exportação: empresa + filtros da tela (comportamento histórico do JobExportaCihCsvFinal).
        // Listagem: escopo de visibilidade (ver_todas / Montisol / vinculados).
        if ($this->isExport) {
            return Cih::query()
                ->with($this->getExportRelationships())
                ->where('empresa_id', $this->user->empresa_id);
        }

        return $this->acessoService->queryBaseComEscopo(
            $this->user,
            $this->getListingRelationships()
        );
    }

    /**
     * Relacionamentos mínimos para cards da listagem.
     */
    private function getListingRelationships(): array
    {
        return [
            'Colaboradores' => function ($query) {
                $query->select(['feedback_curriculos.id', 'feedback_curriculos.curriculo_id']);
            },
            'Colaboradores.Curriculo:id,nome',
            'Colaboradores.Demissao' => function ($query) {
                $query->select(
                    'id',
                    'feedback_id',
                    'data_desmobilizacao',
                    DB::raw('DATEDIFF(NOW(), data_desmobilizacao) AS dias')
                );
            },
            'Colaboradores.Admissao:id,feedback_id,centro_custo_id',
            'Tag:id,label',
            'Area:id,label',
            'CentroDeCusto:id,label',
            'GestorAprovacao:id,nome',
            'ResponsavelLancamento:id,nome',
            'ResponsavelAprovacao:id,nome',
            'RhAprovacao:id,nome',
        ];
    }

    /**
     * Relacionamentos mínimos para exportação CSV (sem Demissao/Gestor — não saem no Excel).
     */
    private function getExportRelationships(): array
    {
        return [
            'Colaboradores' => function ($query) {
                $query->select([
                    'feedback_curriculos.id',
                    'feedback_curriculos.curriculo_id',
                    'feedback_curriculos.vagas_abertas_id',
                ]);
            },
            'Colaboradores.Curriculo:id,nome',
            'Colaboradores.Admissao:id,feedback_id,cargo,pis,centro_custo_id',
            'Colaboradores.Admissao.CentroCusto:id,label',
            'Colaboradores.VagaAberta:id,vaga_id',
            'Colaboradores.VagaAberta.Vaga:id,nome',
            'Tag:id,label',
            'Area:id,label',
            'CentroDeCusto:id,label',
            'ResponsavelLancamento:id,nome',
            'ResponsavelAprovacao:id,nome',
            'RhAprovacao:id,nome',
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
