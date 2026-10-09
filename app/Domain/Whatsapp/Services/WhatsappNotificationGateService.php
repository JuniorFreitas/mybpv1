<?php

namespace App\Domain\Whatsapp\Services;

use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Models\ClienteConfig;
use App\Models\User;
use App\Models\UsuarioWhatsappPreferencia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WhatsappNotificationGateService
{
    public function __construct(
        private readonly WhatsappConfigService $configService,
    ) {
    }

    /**
     * Valida envio para qualquer tipo de mensagem WhatsApp.
     *
     * Só retorna true quando a empresa tem WhatsApp liberado ({@see empresaPermiteWhatsapp})
     * e o módulo do tipo está habilitado ({@see WhatsappConfigService::isModuloHabilitado}).
     */
    public function podeEnviar(
        TipoMensagemWhatsapp $tipo,
        int $empresaId,
        ?int $destinatarioUserId = null,
    ): bool {
        if (!$this->empresaPermiteWhatsapp($empresaId)) {
            return false;
        }

        if (!$this->configService->isModuloHabilitado($empresaId, $tipo->modulo())) {
            Log::info('WhatsApp bloqueado: módulo desabilitado na empresa', [
                'empresa_id' => $empresaId,
                'modulo' => $tipo->modulo(),
                'tipo' => $tipo->value,
            ]);

            return false;
        }

        if ($destinatarioUserId !== null && !$this->usuarioAceitaModulo($destinatarioUserId, $tipo->modulo())) {
            Log::info('WhatsApp bloqueado: preferência do usuário', [
                'user_id' => $destinatarioUserId,
                'modulo' => $tipo->modulo(),
                'tipo' => $tipo->value,
            ]);

            return false;
        }

        return true;
    }

    public function empresaPermiteWhatsapp(int $empresaId): bool
    {
        return (bool) ClienteConfig::query()
            ->where('cliente_id', $empresaId)
            ->value('envia_whatsapp');
    }

    public function podeEnviarAniversario(int $empresaId): bool
    {
        if (!Schema::hasTable('cliente_configs') || !Schema::hasColumn('cliente_configs', 'aniversario_whatsapp')) {
            return false;
        }

        if (!$this->empresaPermiteWhatsapp($empresaId)) {
            return false;
        }

        return (bool) ClienteConfig::query()
            ->where('cliente_id', $empresaId)
            ->value('aniversario_whatsapp');
    }

    public function podeEnviarMfaLogin(int $empresaId): bool
    {
        if (!Schema::hasTable('cliente_configs') || !Schema::hasColumn('cliente_configs', 'mfa_login_whatsapp')) {
            return false;
        }

        if (!$this->empresaPermiteWhatsapp($empresaId)) {
            return false;
        }

        $config = ClienteConfig::query()
            ->where('cliente_id', $empresaId)
            ->first(['mfa_login_habilitado', 'mfa_login_whatsapp']);

        if (!$config) {
            return false;
        }

        return (bool) $config->mfa_login_habilitado && (bool) $config->mfa_login_whatsapp;
    }

    public function usuarioAceitaModulo(int $userId, string $modulo): bool
    {
        $usuario = User::withoutGlobalScopes()
            ->select(['id', 'empresa_id'])
            ->find($userId);

        if (!$usuario) {
            return false;
        }

        if (!$this->empresaPermiteWhatsapp((int) $usuario->empresa_id)) {
            return false;
        }

        if (!$this->configService->isModuloHabilitado((int) $usuario->empresa_id, $modulo)) {
            return false;
        }

        $preferencia = UsuarioWhatsappPreferencia::query()
            ->select(['receber'])
            ->where('user_id', $userId)
            ->where('modulo', $modulo)
            ->first();

        if ($preferencia === null) {
            return true;
        }

        return (bool) $preferencia->receber;
    }

    public function usuarioPodeConfigurarPreferencias(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (!$user) {
            return false;
        }

        return $user->can('preferencias_notificacao_whatsapp');
    }
}
