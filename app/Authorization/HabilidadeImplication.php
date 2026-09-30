<?php

namespace App\Authorization;

/**
 * Regras de implicação entre habilidades (ex.: insert ⇒ access).
 */
final class HabilidadeImplication
{
    private const INSERT_SUFFIX = '_insert';

    /**
     * Se o usuário tem {skill}_insert e {skill} existe no sistema, concede {skill} (access).
     *
     * @param list<string> $habilidadesDoUsuario
     * @param array<string, true>|null $nomesSistemaSet mapa nome => true; null = não valida existência
     * @return list<string>
     */
    public static function expandWithImpliedAccess(array $habilidadesDoUsuario, ?array $nomesSistemaSet = null): array
    {
        $set = [];
        foreach ($habilidadesDoUsuario as $nome) {
            $nome = (string) $nome;
            if ($nome === '') {
                continue;
            }
            $set[$nome] = true;

            if (!str_ends_with($nome, self::INSERT_SUFFIX)) {
                continue;
            }

            $access = substr($nome, 0, -strlen(self::INSERT_SUFFIX));
            if ($access === '' || isset($set[$access])) {
                continue;
            }
            if ($nomesSistemaSet !== null && !isset($nomesSistemaSet[$access])) {
                continue;
            }
            $set[$access] = true;
        }

        return array_keys($set);
    }

    /**
     * @param list<string> $habilidadesDoUsuario
     * @param array<string, true>|null $nomesSistemaSet
     */
    public static function allows(string $habilidade, array $habilidadesDoUsuario, ?array $nomesSistemaSet = null): bool
    {
        $efetivas = self::expandWithImpliedAccess($habilidadesDoUsuario, $nomesSistemaSet);

        return in_array($habilidade, $efetivas, true);
    }
}
