<?php

namespace App\Domain\SaudeOcupacional\Regras\Condicoes;

use App\Domain\SaudeOcupacional\Regras\PerfilColaborador;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/** Cargo vigente do colaborador (vagas.id) está na lista. */
class CondicaoCargo implements CondicaoRegra
{
    public const TIPO = 'cargo';

    public const MAX_CARGOS = 200;

    public function tipo(): string
    {
        return self::TIPO;
    }

    public function rotulo(): string
    {
        return 'Cargo';
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
            throw ValidationException::withMessages(['condicoes' => 'Escolha pelo menos um cargo.']);
        }
        if (count($ids) > self::MAX_CARGOS) {
            throw ValidationException::withMessages(['condicoes' => 'Escolha no máximo ' . self::MAX_CARGOS . ' cargos.']);
        }

        return $ids;
    }

    public function aplica(mixed $valor, PerfilColaborador $perfil, CarbonImmutable $dataReferencia): bool
    {
        return $perfil->vagaId !== null && in_array($perfil->vagaId, array_map('intval', (array) $valor), true);
    }

    public function descrever(mixed $valor, array $rotulos = []): string
    {
        $nomes = collect((array) $valor)
            ->map(fn ($id) => ($rotulos['cargos'] ?? [])[(int) $id] ?? ('#' . (int) $id))
            ->all();

        if (count($nomes) > 3) {
            return 'cargo: ' . implode(', ', array_slice($nomes, 0, 3)) . ' e mais ' . (count($nomes) - 3);
        }

        return 'cargo: ' . implode(', ', $nomes);
    }
}
