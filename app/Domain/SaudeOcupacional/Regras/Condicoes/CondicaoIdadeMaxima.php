<?php

namespace App\Domain\SaudeOcupacional\Regras\Condicoes;

use App\Domain\SaudeOcupacional\Regras\PerfilColaborador;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/** Idade até N anos (inclusive). Ex.: menores de 18 = até 17. Sem data de nascimento: não se aplica. */
class CondicaoIdadeMaxima implements CondicaoRegra
{
    public const TIPO = 'idade_maxima';

    public function tipo(): string
    {
        return self::TIPO;
    }

    public function rotulo(): string
    {
        return 'Idade até';
    }

    public function normalizar(mixed $valor): int
    {
        $idade = filter_var($valor, FILTER_VALIDATE_INT);
        if ($idade === false || $idade < 14 || $idade > 100) {
            throw ValidationException::withMessages(['condicoes' => 'Idade deve ser um número entre 14 e 100.']);
        }

        return $idade;
    }

    public function aplica(mixed $valor, PerfilColaborador $perfil, CarbonImmutable $dataReferencia): bool
    {
        $idade = $perfil->idadeEm($dataReferencia);

        return $idade !== null && $idade <= (int) $valor;
    }

    public function descrever(mixed $valor, array $rotulos = []): string
    {
        return 'idade até ' . (int) $valor . ' anos';
    }
}
