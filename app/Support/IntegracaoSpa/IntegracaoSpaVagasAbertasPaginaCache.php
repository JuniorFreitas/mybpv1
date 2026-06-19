<?php

namespace App\Support\IntegracaoSpa;

use App\Data\IntegracaoSpa\VagasAbertasPagina;
use Illuminate\Support\Facades\Cache;

/**
 * Cache da paginação GET …/vagas-abertas por empresa.
 *
 * Usa um contador de geração por empresa_id: ao alterar vagas, incrementa o gen e as
 * chaves antigas deixam de ser usadas (TTL eventualmente limpa entradas órfãs).
 */
final class IntegracaoSpaVagasAbertasPaginaCache
{
    private static function genKey(int $empresaId): string
    {
        return 'integracao_spa:v2:vagas_gen:'.$empresaId;
    }

    private static function pageKey(int $empresaId, int $page, int $porPagina, int $generation): string
    {
        return 'integracao_spa:v2:vagas_page:'.$empresaId.':'.$page.':'.$porPagina.':g'.$generation;
    }

    public static function ttlSeconds(): int
    {
        $seconds = (int) config('integracao_spa.vagas_abertas_pagina_cache_ttl_seconds', 86400);

        return max(60, $seconds);
    }

    /**
     * Incrementa a geração: todas as páginas dessa empresa passam a usar novo namespace de chave.
     */
    public static function bumpEmpresa(int $empresaId): void
    {
        if ($empresaId < 1) {
            return;
        }

        $key = self::genKey($empresaId);
        $next = (int) Cache::get($key, 0) + 1;
        Cache::put($key, $next, now()->addSeconds(self::ttlSeconds()));
    }

    /**
     * @param  callable(): VagasAbertasPagina  $resolver
     */
    public static function remember(int $empresaId, int $page, int $porPagina, callable $resolver): VagasAbertasPagina
    {
        $gen = (int) Cache::get(self::genKey($empresaId), 0);
        $cacheKey = self::pageKey($empresaId, $page, $porPagina, $gen);

        $payload = Cache::remember($cacheKey, now()->addSeconds(self::ttlSeconds()), function () use ($resolver) {
            return $resolver()->toArray();
        });

        if (! is_array($payload)) {
            return $resolver();
        }

        return VagasAbertasPagina::fromSpaCacheArray($payload);
    }
}
