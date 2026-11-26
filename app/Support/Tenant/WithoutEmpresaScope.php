<?php

namespace App\Support\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Helper para bypass consciente do ScopeEmpresa.
 * Use em jobs/fluxos públicos quando precisar remover o scope e
 * reaplicar o filtro de empresa de forma explícita.
 */
final class WithoutEmpresaScope
{
    /**
     * @template TModel of Model
     * @param  class-string<TModel>  $modelClass
     * @param  callable(Builder): mixed  $callback
     */
    public static function forEmpresa(string $modelClass, int $empresaId, callable $callback): mixed
    {
        if ($empresaId <= 0) {
            throw new \InvalidArgumentException('empresa_id inválido para WithoutEmpresaScope.');
        }

        /** @var Builder $query */
        $query = $modelClass::withoutGlobalScopes()->where('empresa_id', $empresaId);

        return $callback($query);
    }
}
