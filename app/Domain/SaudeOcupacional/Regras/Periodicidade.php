<?php

namespace App\Domain\SaudeOcupacional\Regras;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

/**
 * Validade de um ASO.
 * - anos: realização + N anos − 1 dia
 * - dias: realização + N dias
 */
final class Periodicidade
{
    public const UNIDADE_ANOS = 'anos';

    public const UNIDADE_DIAS = 'dias';

    public function __construct(
        public readonly string $unidade,
        public readonly int $valor
    ) {
        if (!in_array($unidade, [self::UNIDADE_ANOS, self::UNIDADE_DIAS], true)) {
            throw new InvalidArgumentException('Unidade de periodicidade inválida.');
        }
        if ($valor < 1) {
            throw new InvalidArgumentException('Periodicidade deve ser maior que zero.');
        }
    }

    /**
     * @param  array{unidade?: string, valor?: int|string}  $dados
     */
    public static function deArray(array $dados): self
    {
        return new self((string) ($dados['unidade'] ?? ''), (int) ($dados['valor'] ?? 0));
    }

    public function vencimentoPara(CarbonImmutable $realizacao): CarbonImmutable
    {
        $base = $realizacao->startOfDay();

        if ($this->unidade === self::UNIDADE_DIAS) {
            return $base->addDays($this->valor);
        }

        return $base->addYears($this->valor)->subDay();
    }

    public function descricao(): string
    {
        if ($this->unidade === self::UNIDADE_DIAS) {
            return $this->valor . ($this->valor === 1 ? ' dia' : ' dias');
        }

        return $this->valor . ($this->valor === 1 ? ' ano' : ' anos');
    }

    /**
     * @return array{unidade: string, valor: int}
     */
    public function toArray(): array
    {
        return ['unidade' => $this->unidade, 'valor' => $this->valor];
    }
}
