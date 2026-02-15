<?php

namespace App\Observers;

use App\Helpers\RHHelper;
use App\Models\User;

class UserHabilidadeRHCacheObserver
{
    /**
     * Habilidades que quando atribuídas/removidas invalidam cache RH
     */
    private const HABILIDADES_RH = [
        'privilegio_gestao_rh',
        'privilegio_aprovar_por_rh',
        'privilegio_aprovar_rh'
    ];

    /**
     * Handle quando uma habilidade é anexada ao usuário
     * via pivot table users_habilidades
     *
     * @param mixed $relation
     * @param array $pivotIds
     * @param array $pivotIdsAttributes
     * @return void
     */
    public function pivotAttached($relation, $pivotIds, $pivotIdsAttributes)
    {
        // Busca nomes das habilidades anexadas
        $habilidades = \App\Models\Habilidade::whereIn('id', $pivotIds)
            ->pluck('nome')
            ->toArray();

        // Se anexou habilidade RH, invalida cache
        if (!empty(array_intersect($habilidades, self::HABILIDADES_RH))) {
            $user = User::find($relation->first()->user_id ?? null);

            if ($user) {
                RHHelper::invalidarCache($user->empresa_id);

                \Log::info("Cache RH invalidado - User #{$user->id} ganhou privilégio RH");
            }
        }
    }

    /**
     * Handle quando uma habilidade é desanexada do usuário
     * via pivot table users_habilidades
     *
     * @param mixed $relation
     * @param array $pivotIds
     * @return void
     */
    public function pivotDetached($relation, $pivotIds)
    {
        // Busca nomes das habilidades removidas
        $habilidades = \App\Models\Habilidade::whereIn('id', $pivotIds)
            ->pluck('nome')
            ->toArray();

        // Se removeu habilidade RH, invalida cache
        if (!empty(array_intersect($habilidades, self::HABILIDADES_RH))) {
            $user = User::find($relation->first()->user_id ?? null);

            if ($user) {
                RHHelper::invalidarCache($user->empresa_id);

                \Log::info("Cache RH invalidado - User #{$user->id} perdeu privilégio RH");
            }
        }
    }
}
