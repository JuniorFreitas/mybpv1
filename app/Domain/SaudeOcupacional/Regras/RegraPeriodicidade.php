<?php

namespace App\Domain\SaudeOcupacional\Regras;

/**
 * Regra ativa já pronta para o motor (sem Eloquent).
 */
final class RegraPeriodicidade
{
    /**
     * @param  list<int>  $exameTipoIds
     * @param  list<array{tipo: string, valor: mixed}>  $condicoes
     */
    public function __construct(
        public readonly int $id,
        public readonly string $nome,
        public readonly array $exameTipoIds,
        public readonly array $condicoes,
        public readonly Periodicidade $periodicidade
    ) {
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public static function deArray(array $dados): self
    {
        return new self(
            (int) $dados['id'],
            (string) $dados['nome'],
            array_map('intval', (array) $dados['exame_tipo_ids']),
            (array) $dados['condicoes'],
            new Periodicidade((string) $dados['unidade'], (int) $dados['valor'])
        );
    }

    /** Lista vazia = todos os tipos com vencimento (usado pelas regras de mercado NR-7). */
    public function valeParaTipo(int $exameTipoId): bool
    {
        return $this->exameTipoIds === [] || in_array($exameTipoId, $this->exameTipoIds, true);
    }

    /** Regra do Padrão NR-7 (id negativo): não editável, não aparece na lista da empresa. */
    public function ehRegraDeMercado(): bool
    {
        return $this->id < 0;
    }
}
