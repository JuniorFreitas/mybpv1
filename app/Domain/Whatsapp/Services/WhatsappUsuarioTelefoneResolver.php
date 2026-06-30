<?php

namespace App\Domain\Whatsapp\Services;

use App\Models\TelefoneCurriculo;

class WhatsappUsuarioTelefoneResolver
{
    public function resolverNumeroEnvio(int $userId): ?string
    {
        $telefone = $this->resolverRegistroEnvio($userId);

        return $telefone?->sonumero;
    }

    public function resolverRegistroEnvio(int $userId): ?TelefoneCurriculo
    {
        $telefone = TelefoneCurriculo::query()
            ->select(['id', 'curriculo_id', 'tipo', 'numero', 'principal', 'pais'])
            ->where('curriculo_id', $userId)
            ->where('tipo', TelefoneCurriculo::TIPO_WHATS)
            ->where('principal', true)
            ->whereNotNull('numero')
            ->where('numero', '!=', '')
            ->first();

        if ($telefone) {
            return $telefone;
        }

        return TelefoneCurriculo::query()
            ->select(['id', 'curriculo_id', 'tipo', 'numero', 'principal', 'pais'])
            ->where('curriculo_id', $userId)
            ->where('tipo', TelefoneCurriculo::TIPO_WHATS)
            ->whereNotNull('numero')
            ->where('numero', '!=', '')
            ->first();
    }

    /** @return array{tem_telefone: bool, tem_whatsapp: bool, whatsapp_principal: bool, tipo: ?string, numero: ?string, numero_mascarado: ?string} */
    public function resolverStatus(int $userId): array
    {
        $telefones = TelefoneCurriculo::query()
            ->select(['id', 'tipo', 'numero', 'principal'])
            ->where('curriculo_id', $userId)
            ->whereNotNull('numero')
            ->where('numero', '!=', '')
            ->get();

        $principal = $telefones->firstWhere('principal', true);
        $whatsapp = $this->resolverRegistroEnvio($userId);
        $numero = $whatsapp?->numero ?? $principal?->numero;

        return [
            'tem_telefone' => $telefones->isNotEmpty(),
            'tem_whatsapp' => $whatsapp !== null,
            'whatsapp_principal' => $whatsapp !== null && (bool) $whatsapp->principal,
            'tipo' => $principal?->tipo ?? $whatsapp?->tipo,
            'numero' => $numero,
            'numero_mascarado' => $numero ? $this->mascararNumero($numero) : null,
        ];
    }

    private function mascararNumero(string $numero): string
    {
        $digitos = preg_replace('/\D+/', '', $numero) ?? '';

        if (strlen($digitos) < 4) {
            return $digitos;
        }

        return str_repeat('*', max(0, strlen($digitos) - 4)) . substr($digitos, -4);
    }
}
