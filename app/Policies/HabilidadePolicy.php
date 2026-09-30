<?php

namespace App\Policies;

use App\Authorization\HabilidadeResolver;
use App\Models\User;

/**
 * Policy genérica de habilidade MyBP (wrapper do HabilidadeResolver).
 * Uso: $this->authorize('habilidade', 'admissao_cih') via Gate::define abaixo,
 * ou Gate::inspect / authorize com ability nomeada.
 */
class HabilidadePolicy
{
    public function __construct(
        private readonly HabilidadeResolver $resolver,
    ) {
    }

    public function access(User $user, string $habilidade): bool
    {
        return $this->resolver->can($user, $habilidade);
    }
}
