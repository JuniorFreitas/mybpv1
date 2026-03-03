<?php

namespace App\Observers;

use App\Helpers\RHHelper;
use App\Models\User;

class UserRHCacheObserver
{
    /**
     * Atributos que quando alterados invalidam o cache de RH
     */
    private const ATRIBUTOS_MONITORADOS = ['ativo', 'login', 'nome', 'tipo'];

    /**
     * Handle the User "updated" event.
     * Invalida cache quando usuário RH muda atributos relevantes
     *
     * @param User $user
     * @return void
     */
    public function updated(User $user)
    {
        // Verifica se algum atributo monitorado foi alterado
        $atributosAlterados = array_intersect(
            array_keys($user->getDirty()),
            self::ATRIBUTOS_MONITORADOS
        );

        if (!empty($atributosAlterados)) {
            // Invalida cache se for usuário RH OU se mudou status/tipo
            if ($this->deveInvalidarCache($user)) {
                if ($user->empresa_id) {
                    RHHelper::invalidarCache($user->empresa_id);
                } else {
                    \Log::warning("Cache RH nao invalidado - empresa_id ausente para user #{$user->id}");
                }

                \Log::info("Cache RH invalidado - User #{$user->id} alterou: " . implode(', ', $atributosAlterados));
            }
        }
    }

    /**
     * Handle the User "deleted" event.
     * Invalida cache quando usuário RH é deletado
     *
     * @param User $user
     * @return void
     */
    public function deleted(User $user)
    {
        if (RHHelper::ehUsuarioRH($user)) {
            if ($user->empresa_id) {
                RHHelper::invalidarCache($user->empresa_id);
            } else {
                \Log::warning("Cache RH nao invalidado - empresa_id ausente para user #{$user->id}");
            }

            \Log::info("Cache RH invalidado - User RH #{$user->id} deletado");
        }
    }

    /**
     * Verifica se deve invalidar cache baseado nas mudanças
     *
     * @param User $user
     * @return bool
     */
    private function deveInvalidarCache(User $user): bool
    {
        // Se mudou 'ativo' ou 'tipo', sempre invalida (pode ter perdido privilégio RH)
        if ($user->isDirty('ativo') || $user->isDirty('tipo')) {
            return true;
        }

        // Se mudou 'login' ou 'nome', invalida apenas se for usuário RH
        if ($user->isDirty('login') || $user->isDirty('nome')) {
            return RHHelper::ehUsuarioRH($user);
        }

        return false;
    }
}
