<?php

namespace App\Domain\SaudeOcupacional\Regras\Condicoes;

use App\Domain\SaudeOcupacional\Regras\PerfilColaborador;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/** Cargo vigente está exposto a pelo menos um dos riscos da lista (PGR vigente). */
class CondicaoRisco implements CondicaoRegra
{
    public const TIPO = 'risco';

    public const MAX_RISCOS = 100;

    public function tipo(): string
    {
        return self::TIPO;
    }

    public function rotulo(): string
    {
        return 'Exposto a um destes riscos';
    }

    /**
     * @return list<int>
     */
    public function normalizar(mixed $valor): array
    {
        $ids = collect(is_array($valor) ? $valor : [])
            ->map(fn ($id) => filter_var($id, FILTER_VALIDATE_INT))
            ->filter(fn ($id) => $id !== false && $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            throw ValidationException::withMessages(['condicoes' => 'Escolha pelo menos um risco.']);
        }
        if (count($ids) > self::MAX_RISCOS) {
            throw ValidationException::withMessages(['condicoes' => 'Escolha no máximo ' . self::MAX_RISCOS . ' riscos.']);
        }

        return $ids;
    }

    public function aplica(mixed $valor, PerfilColaborador $perfil, CarbonImmutable $dataReferencia): bool
    {
        return array_intersect($perfil->riscoIds, array_map('intval', (array) $valor)) !== [];
    }

    public function descrever(mixed $valor, array $rotulos = []): string
    {
        $nomes = collect((array) $valor)
            ->map(fn ($id) => ($rotulos['riscos'] ?? [])[(int) $id] ?? ('#' . (int) $id))
            ->all();

        if (count($nomes) > 3) {
            return 'exposto a: ' . implode(', ', array_slice($nomes, 0, 3)) . ' e mais ' . (count($nomes) - 3);
        }

        return 'exposto a: ' . implode(', ', $nomes);
    }
}
