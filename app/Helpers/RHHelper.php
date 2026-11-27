<?php

namespace App\Helpers;

use App\Models\NotificacaoTipo;
use App\Models\User;
use App\Services\Notificacao\NotificacaoEngine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RHHelper
{
    /** Cache TTL candidatos RH (tela/config): 15 minutos */
    private const CACHE_TTL_CANDIDATOS = 900;

    /**
     * Usuários com privilégio RH (para UI de configuração / checagens).
     * Não define mais quem recebe e-mail de aprovação — isso vem dos grupos.
     *
     * @return Collection<int, User>
     */
    public static function buscarUsuariosRH(int $empresaId): Collection
    {
        $cacheKey = self::getCacheKeyCandidatos($empresaId);

        $loader = function () use ($empresaId) {
            return User::withoutGlobalScopes()
                ->select('users.id', 'users.nome', 'users.login', 'users.empresa_id', 'users.ativo', 'users.tipo')
                ->join('papeis', 'users.grupo_id', '=', 'papeis.id')
                ->join('papeis_habilidades', 'papeis.id', '=', 'papeis_habilidades.papel_id')
                ->join('habilidades', 'papeis_habilidades.habilidade_id', '=', 'habilidades.id')
                ->where('users.empresa_id', $empresaId)
                ->where('users.ativo', true)
                ->whereNull('users.deleted_at')
                ->where('users.tipo', '!=', 'Empresa')
                ->whereIn('habilidades.nome', ['privilegio_gestao_rh', 'privilegio_aprovar_por_rh', 'privilegio_aprovar_rh'])
                ->distinct()
                ->get();
        };

        if (!method_exists(Cache::getStore(), 'tags')) {
            return $loader();
        }

        return Cache::tags(['rh_usuarios', "empresa_{$empresaId}"])->remember(
            $cacheKey,
            self::CACHE_TTL_CANDIDATOS,
            $loader
        );
    }

    /**
     * E-mails RH para notificação de aprovação.
     * Fonte: grupos cadastrados em Configurações › Notificações (tipo aprovacao_rh).
     * Sem grupo compatível = [].
     *
     * @param  array{centro_custo_id?: int|null, matriz?: bool|null}  $contexto
     * @return list<string>
     */
    public static function buscarEmailsRH(int $empresaId, array $contexto = []): array
    {
        return app(NotificacaoEngine::class)->emailsAprovacaoRh($empresaId, $contexto);
    }

    public static function ehUsuarioRH(User $user): bool
    {
        return $user->can('privilegio_gestao_rh')
            || $user->can('privilegio_aprovar_por_rh')
            || $user->can('privilegio_aprovar_rh');
    }

    public static function invalidarCache(int $empresaId): void
    {
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags(["empresa_{$empresaId}"])->flush();
        }
        app(NotificacaoEngine::class)->invalidarEmpresa($empresaId);
    }

    public static function invalidarTodoCache(): void
    {
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags(['rh_usuarios'])->flush();
            Cache::tags(['notificacao_destinatarios'])->flush();
        }
    }

    /**
     * @return list<int>
     */
    public static function idsUsuariosRhEmpresa(int $empresaId): array
    {
        return self::buscarUsuariosRH($empresaId)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private static function getCacheKeyCandidatos(int $empresaId): string
    {
        return 'rh_candidatos_empresa_' . $empresaId . '_' . NotificacaoTipo::APROVACAO_RH;
    }
}
