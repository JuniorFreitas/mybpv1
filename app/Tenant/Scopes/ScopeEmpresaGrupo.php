<?php

namespace App\Tenant\Scopes;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Como ScopeEmpresa, mas em vez de escopar por uma única empresa, escopa
 * por todas as empresas do mesmo grupo da empresa ativa. Usado por
 * recursos que devem ser compartilhados dentro do grupo (hoje: Vaga/cargo)
 * — ao contrário de dados operacionais (currículos, admissões, avaliações)
 * que continuam isolados por empresa via ScopeEmpresa normal.
 */
class ScopeEmpresaGrupo implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (!auth()->user()) {
            return;
        }

        $empresaIds = self::empresaIdsDoGrupo(auth()->user()->empresaAtivaId());

        $builder->whereIn($model->getTable() . '.empresa_id', $empresaIds);
    }

    /**
     * IDs de todas as empresas do mesmo grupo de $empresaId (inclui a
     * própria $empresaId). Se ela não pertencer a nenhum grupo, retorna só
     * ela mesma.
     *
     * @return array<int>
     */
    public static function empresaIdsDoGrupo(int $empresaId): array
    {
        $grupoId = cache()->remember(
            'empresa_grupo_id_' . $empresaId,
            now()->addHours(6),
            fn () => Cliente::withoutGlobalScopes()->whereKey($empresaId)->value('grupo_id')
        );

        if (!$grupoId) {
            return [$empresaId];
        }

        $empresaIds = cache()->remember(
            'empresas_do_grupo_' . $grupoId,
            now()->addHours(6),
            fn () => Cliente::withoutGlobalScopes()->where('grupo_id', $grupoId)->pluck('id')->all()
        );

        return is_array($empresaIds) ? $empresaIds : collect($empresaIds)->all();
    }
}
