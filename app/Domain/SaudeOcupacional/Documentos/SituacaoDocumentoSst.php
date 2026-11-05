<?php

namespace App\Domain\SaudeOcupacional\Documentos;

use Carbon\CarbonImmutable;

/**
 * Situação de um documento SST pela vigência da versão atual.
 * Sem fim de vigência → "sem_validade" (ex.: ASO, certificado sem prazo).
 */
final class SituacaoDocumentoSst
{
    public const VIGENTE = 'vigente';

    public const VENCENDO = 'vencendo';

    public const VENCIDO = 'vencido';

    public const SEM_VALIDADE = 'sem_validade';

    public const JANELA_VENCENDO_DIAS = 60;

    public const ROTULOS = [
        self::VIGENTE => 'Vigente',
        self::VENCENDO => 'Vencendo',
        self::VENCIDO => 'Vencido',
        self::SEM_VALIDADE => 'Sem validade',
    ];

    public static function de(?string $vigenciaFim, CarbonImmutable $hoje): string
    {
        if (!$vigenciaFim) {
            return self::SEM_VALIDADE;
        }

        $fim = CarbonImmutable::parse($vigenciaFim)->startOfDay();
        if ($fim->lessThan($hoje->startOfDay())) {
            return self::VENCIDO;
        }

        return $fim->lessThanOrEqualTo($hoje->addDays(self::JANELA_VENCENDO_DIAS)) ? self::VENCENDO : self::VIGENTE;
    }
}
