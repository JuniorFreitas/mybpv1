<?php

namespace App\Domain\Whatsapp\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Contadores diários (cache com TTL) + log estruturado de envios WhatsApp bloqueados pelo gate.
 * Observabilidade leve para Horizon/suporte — sem dashboard dedicado nesta entrega.
 */
class WhatsappBloqueioMetrics
{
    public const TTL_SEGUNDOS = 604800; // 7 dias

    public const MOTIVO_META_AUSENTE = 'meta_ausente';
    public const MOTIVO_META_INVALIDA = 'meta_invalida';
    public const MOTIVO_EMPRESA = 'empresa_nao_liberada';
    public const MOTIVO_MODULO = 'modulo_desabilitado';
    public const MOTIVO_PREFERENCIA = 'preferencia_usuario';
    public const MOTIVO_GATE = 'gate_recusou';

    /**
     * @param  array<string, mixed>  $contexto  Sem dados sensíveis (telefone, mensagem, CPF).
     */
    public function registrar(string $motivo, ?int $empresaId = null, array $contexto = []): void
    {
        $dia = now()->format('Y-m-d');
        $totalDia = $this->incrementar($this->chaveTotal($dia));

        if ($empresaId !== null && $empresaId > 0) {
            $this->incrementar($this->chaveEmpresa($empresaId, $dia));
        }

        $this->incrementar($this->chaveMotivo($motivo, $dia));

        Log::info('WhatsApp bloqueado pelo gate', array_filter([
            'motivo' => $motivo,
            'empresa_id' => $empresaId,
            'total_dia' => $totalDia,
            'tipo' => $contexto['tipo'] ?? null,
            'modulo' => $contexto['modulo'] ?? null,
            'destinatario_user_id' => $contexto['destinatario_user_id'] ?? null,
        ], static fn ($v) => $v !== null));
    }

    public function totalDia(?string $dia = null): int
    {
        return (int) Cache::get($this->chaveTotal($dia ?? now()->format('Y-m-d')), 0);
    }

    public function totalEmpresa(int $empresaId, ?string $dia = null): int
    {
        return (int) Cache::get($this->chaveEmpresa($empresaId, $dia ?? now()->format('Y-m-d')), 0);
    }

    public function totalMotivo(string $motivo, ?string $dia = null): int
    {
        return (int) Cache::get($this->chaveMotivo($motivo, $dia ?? now()->format('Y-m-d')), 0);
    }

    private function incrementar(string $chave): int
    {
        $atual = (int) Cache::get($chave, 0) + 1;
        Cache::put($chave, $atual, self::TTL_SEGUNDOS);

        return $atual;
    }

    private function chaveTotal(string $dia): string
    {
        return "whatsapp:bloqueios:{$dia}";
    }

    private function chaveEmpresa(int $empresaId, string $dia): string
    {
        return "whatsapp:bloqueios:empresa:{$empresaId}:{$dia}";
    }

    private function chaveMotivo(string $motivo, string $dia): string
    {
        return "whatsapp:bloqueios:motivo:{$motivo}:{$dia}";
    }
}
