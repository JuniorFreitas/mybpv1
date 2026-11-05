<?php

namespace App\Domain\SaudeOcupacional\Regras;

use Carbon\CarbonImmutable;

final class ResultadoVencimento
{
    public function __construct(
        public readonly CarbonImmutable $vencimento,
        public readonly Periodicidade $periodicidade,
        public readonly ?RegraPeriodicidade $regra = null
    ) {
    }

    public function motivo(): string
    {
        if ($this->regra) {
            return sprintf('Regra "%s": %s', $this->regra->nome, $this->periodicidade->descricao());
        }

        return 'Padrão do tipo de exame: ' . $this->periodicidade->descricao();
    }
}
