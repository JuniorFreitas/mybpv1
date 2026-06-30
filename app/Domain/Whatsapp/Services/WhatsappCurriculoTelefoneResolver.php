<?php

namespace App\Domain\Whatsapp\Services;

use App\Models\TelefoneCurriculo;

class WhatsappCurriculoTelefoneResolver
{
    public function telefonePrincipalPermiteWhatsapp(?TelefoneCurriculo $telefone): bool
    {
        if ($telefone === null) {
            return false;
        }

        if ($telefone->tipo !== TelefoneCurriculo::TIPO_WHATS) {
            return false;
        }

        $numero = preg_replace('/\D+/', '', (string) $telefone->numero) ?? '';

        return strlen($numero) >= 10;
    }

    public function resolverPrincipalWhatsapp(int $curriculoId): ?TelefoneCurriculo
    {
        $telefone = TelefoneCurriculo::query()
            ->select(['id', 'curriculo_id', 'tipo', 'numero', 'pais', 'principal'])
            ->where('curriculo_id', $curriculoId)
            ->where('principal', true)
            ->first();

        return $this->telefonePrincipalPermiteWhatsapp($telefone) ? $telefone : null;
    }
}
