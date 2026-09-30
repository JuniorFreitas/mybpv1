<?php

namespace App\Authorization;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Resolve checagens de habilidade considerando aliases do registry.
 */
class HabilidadeResolver
{
    public function __construct(
        private readonly HabilidadeRegistry $registry,
    ) {
    }

    public function can(User $user, string $nomeOuAlias): bool
    {
        $canonico = $this->registry->resolveAlias($nomeOuAlias);

        if ($user->can($canonico)) {
            return true;
        }

        if ($nomeOuAlias !== $canonico && $user->can($nomeOuAlias)) {
            return true;
        }

        // Fallback sem Gate (testes / contextos sem CarregaHabilidades): insert ⇒ access
        $lista = $user->listaDeHabilidades();
        if (HabilidadeImplication::allows($canonico, $lista)) {
            return true;
        }

        return false;
    }

    /**
     * @param list<string> $nomesOuAliases
     */
    public function canAny(User $user, array $nomesOuAliases): bool
    {
        foreach ($nomesOuAliases as $nome) {
            if ($this->can($user, $nome)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $nomesOuAliases
     */
    public function canAll(User $user, array $nomesOuAliases): bool
    {
        foreach ($nomesOuAliases as $nome) {
            if (!$this->can($user, $nome)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Lista de habilidades do usuário com aliases já resolvidos para o canônico.
     *
     * @return list<string>
     */
    public function habilidadesCanonicas(User $user): array
    {
        $set = [];
        foreach (HabilidadeImplication::expandWithImpliedAccess($user->listaDeHabilidades()) as $nome) {
            $set[$this->registry->resolveAlias((string) $nome)] = true;
        }

        return array_keys($set);
    }

    public function allows(string $nomeOuAlias): bool
    {
        $canonico = $this->registry->resolveAlias($nomeOuAlias);

        return Gate::allows($canonico) || ($nomeOuAlias !== $canonico && Gate::allows($nomeOuAlias));
    }
}
