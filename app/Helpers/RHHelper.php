<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RHHelper
{
    /** Cache TTL: 24 horas (86400 segundos) */
    private const CACHE_TTL = 86400;

    /**
     * Busca todos os usuários RH ativos da empresa com CACHE de 24h
     * (com privilegio_gestao_rh OU privilegio_aprovar_por_rh OU privilegio_aprovar_rh)
     *
     * ✅ SELECT otimizado: apenas id, nome, login, empresa_id
     * ✅ Cache invalidado automaticamente via UserObserver
     *
     * @param int $empresaId
     * @return Collection
     */
    public static function buscarUsuariosRH(int $empresaId): Collection
    {
        $cacheKey = self::getCacheKey($empresaId);
        Log::info("RHHelper@buscarUsuariosRH - Buscando usuários RH para empresa ID: {$empresaId} - Cache Key: {$cacheKey}");

        return Cache::tags(['rh_usuarios', "empresa_{$empresaId}"])->remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($empresaId) {
                $usuarios = User::withoutGlobalScopes()
                    ->select('users.id', 'users.nome', 'users.login', 'users.empresa_id', 'users.ativo', 'users.tipo')
                    ->join('papeis', 'users.grupo_id', '=', 'papeis.id')
                    ->join('papeis_habilidades', 'papeis.id', '=', 'papeis_habilidades.papel_id')
                    ->join('habilidades', 'papeis_habilidades.habilidade_id', '=', 'habilidades.id')
                    ->where('users.empresa_id', $empresaId)
                    ->where('users.ativo', true)
                    ->where('users.tipo', '!=', 'Empresa')
                    ->whereIn('habilidades.nome', ['privilegio_gestao_rh', 'privilegio_aprovar_por_rh', 'privilegio_aprovar_rh'])
                    ->distinct()
                    ->get();

                Log::info("RHHelper@buscarUsuariosRH - Encontrados {$usuarios->count()} usuários RH", [
                    'usuarios' => $usuarios->pluck('login')->toArray()
                ]);

                return $usuarios;
            }
        );
    }
    /**
     * Busca apenas os emails dos usuários RH ativos da empresa (CACHE)
     *
     * @param int $empresaId
     * @return array
     */
    public static function buscarEmailsRH(int $empresaId): array
    {
        return self::buscarUsuariosRH($empresaId)
            ->pluck('login')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Verifica se o usuário tem privilégio de RH
     *
     * @param User $user
     * @return bool
     */
    public static function ehUsuarioRH(User $user): bool
    {
        return $user->can('privilegio_gestao_rh') || $user->can('privilegio_aprovar_por_rh') || $user->can('privilegio_aprovar_rh');
    }

    /**
     * Invalida cache de usuários RH de uma empresa específica
     * Chamado automaticamente pelo UserObserver
     *
     * @param int $empresaId
     * @return void
     */
    public static function invalidarCache(int $empresaId): void
    {
        Cache::tags(["empresa_{$empresaId}"])->flush();
    }

    /**
     * Invalida cache de todas as empresas
     * Usar apenas em situações excepcionais
     *
     * @return void
     */
    public static function invalidarTodoCache(): void
    {
        Cache::tags(['rh_usuarios'])->flush();
    }

    /**
     * Gera chave de cache única por empresa
     *
     * @param int $empresaId
     * @return string
     */
    private static function getCacheKey(int $empresaId): string
    {
        return "rh_usuarios_empresa_{$empresaId}";
    }
}
