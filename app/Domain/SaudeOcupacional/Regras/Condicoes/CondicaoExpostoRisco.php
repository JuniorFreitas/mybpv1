<?php

namespace App\Domain\SaudeOcupacional\Regras\Condicoes;

use App\Domain\SaudeOcupacional\Regras\PerfilColaborador;
use Carbon\CarbonImmutable;

/** Cargo vigente tem ao menos um risco no PGR vigente da empresa. */
class CondicaoExpostoRisco implements CondicaoRegra
{
    public const TIPO = 'exposto_risco';

    public function tipo(): string
    {
        return self::TIPO;
    }

    public function rotulo(): string
    {
        return 'Exposto a risco do PGR';
    }

    public function normalizar(mixed $valor): bool
    {
        return true;
    }

    public function aplica(mixed $valor, PerfilColaborador $perfil, CarbonImmutable $dataReferencia): bool
    {
        return $perfil->expostoARisco();
    }

    public function descrever(mixed $valor, array $rotulos = []): string
    {
        return 'exposto a risco do PGR';
    }
}
