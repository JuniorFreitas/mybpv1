<?php

namespace App\Domain\SaudeOcupacional\Painel;

use Illuminate\Support\Facades\Cache;

/**
 * Cache do Painel por empresa, com chave versionada.
 * invalidar() troca a versão: o próximo acesso recalcula.
 */
class PainelCache
{
    public const TTL_SEGUNDOS = 300;

    private const TTL_VERSAO_SEGUNDOS = 86400;

    public static function chave(int $empresaId): string
    {
        return sprintf('saude_ocupacional:painel:%d:v%d', $empresaId, self::versao($empresaId));
    }

    public static function invalidar(?int $empresaId): void
    {
        if (!$empresaId) {
            return;
        }

        Cache::put(self::chaveVersao($empresaId), self::versao($empresaId) + 1, self::TTL_VERSAO_SEGUNDOS);
    }

    private static function versao(int $empresaId): int
    {
        return (int) Cache::get(self::chaveVersao($empresaId), 1);
    }

    private static function chaveVersao(int $empresaId): string
    {
        return 'saude_ocupacional:painel:versao:' . $empresaId;
    }
}
