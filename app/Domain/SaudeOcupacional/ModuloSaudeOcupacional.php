<?php

namespace App\Domain\SaudeOcupacional;

use App\Models\ClienteConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Diz se o módulo Saúde Ocupacional está ligado para a empresa
 * (ClienteConfig.modulo_saude_ocupacional).
 */
class ModuloSaudeOcupacional
{
    private const TTL_SEGUNDOS = 600;

    private const TTL_VERSAO_SEGUNDOS = 2592000; // 30 dias

    private const CHAVE_VERSAO = 'saude_ocupacional:modulo:versao';

    public static function habilitado(?int $empresaId = null): bool
    {
        $empresaId = $empresaId ?: (auth()->check() ? (int) auth()->user()->empresa_id : null);
        if (!$empresaId) {
            return false;
        }

        $chave = sprintf('saude_ocupacional:modulo:v%d:%d', self::versao(), $empresaId);

        return (bool) Cache::remember($chave, self::TTL_SEGUNDOS, function () use ($empresaId) {
            if (!Schema::hasColumn('cliente_configs', 'modulo_saude_ocupacional')) {
                return false;
            }

            return (bool) ClienteConfig::withoutGlobalScopes()
                ->where('cliente_id', $empresaId)
                ->value('modulo_saude_ocupacional');
        });
    }

    /** Invalida o cache de todas as empresas (chamado ao salvar a config do cliente). */
    public static function limparCache(): void
    {
        Cache::put(self::CHAVE_VERSAO, self::versao() + 1, self::TTL_VERSAO_SEGUNDOS);
    }

    private static function versao(): int
    {
        return (int) Cache::get(self::CHAVE_VERSAO, 1);
    }
}
