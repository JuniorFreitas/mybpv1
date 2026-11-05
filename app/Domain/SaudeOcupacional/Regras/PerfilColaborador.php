<?php

namespace App\Domain\SaudeOcupacional\Regras;

use Carbon\CarbonImmutable;

/**
 * Dados do colaborador usados pelas condições das regras.
 * vagaId = cargo vigente (catálogo `vagas`): mudança de cargo aprovada > cargo da admissão.
 * riscoIds = riscos do cargo vigente no PGR vigente da empresa (vazio = não exposto).
 */
final class PerfilColaborador
{
    /**
     * @param  list<int>  $riscoIds
     */
    public function __construct(
        public readonly int $feedbackId,
        public readonly ?CarbonImmutable $nascimento,
        public readonly ?int $vagaId,
        public readonly array $riscoIds = []
    ) {
    }

    public function idadeEm(CarbonImmutable $data): ?int
    {
        if (!$this->nascimento || $this->nascimento->greaterThan($data)) {
            return null;
        }

        return (int) $this->nascimento->diffInYears($data);
    }

    public function expostoARisco(): bool
    {
        return $this->riscoIds !== [];
    }

    /**
     * Mesmo colaborador com outro mapa de riscos (prévia de um PGR ainda não publicado).
     *
     * @param  array<int, list<int>>  $riscosPorCargo
     */
    public function comRiscosDe(array $riscosPorCargo): self
    {
        return new self(
            $this->feedbackId,
            $this->nascimento,
            $this->vagaId,
            $this->vagaId ? array_values($riscosPorCargo[$this->vagaId] ?? []) : []
        );
    }
}
