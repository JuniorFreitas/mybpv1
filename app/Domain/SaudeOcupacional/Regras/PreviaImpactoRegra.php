<?php

namespace App\Domain\SaudeOcupacional\Regras;

/**
 * "Esta regra muda o vencimento de N ASOs": compara as regras de hoje x com a regra proposta
 * (nova, editada ou removida). Usa o ComparadorCenariosVencimento.
 */
class PreviaImpactoRegra
{
    public function __construct(
        private readonly RegrasAtivasRepositorio $regrasAtivas,
        private readonly ComparadorCenariosVencimento $comparador
    ) {
    }

    /**
     * @param  RegraPeriodicidade|null  $proposta  null = remover/desativar a regra $regraId
     * @return array{avaliados: int, alterados: int, antecipados: int, adiados: int, exemplos: list<array<string, string>>, limite_atingido: bool}
     */
    public function calcular(int $empresaId, ?RegraPeriodicidade $proposta, ?int $regraId): array
    {
        $atuais = $this->regrasAtivas->daEmpresa($empresaId);
        $novas = array_values(array_filter($atuais, fn (RegraPeriodicidade $r) => !$regraId || $r->id !== $regraId));
        if ($proposta) {
            $novas[] = $proposta;
        }

        $tipos = collect([$proposta, ...array_filter($atuais, fn ($r) => $regraId && $r->id === $regraId)])
            ->filter()
            ->flatMap(fn (RegraPeriodicidade $r) => $r->exameTipoIds)
            ->unique()
            ->values()
            ->all();

        if ($tipos === []) {
            return ['avaliados' => 0, 'alterados' => 0, 'antecipados' => 0, 'adiados' => 0, 'exemplos' => [], 'limite_atingido' => false];
        }

        return $this->comparador->comparar($empresaId, $tipos, $atuais, $novas);
    }
}
