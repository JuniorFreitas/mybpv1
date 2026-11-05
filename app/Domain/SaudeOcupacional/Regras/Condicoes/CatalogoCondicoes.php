<?php

namespace App\Domain\SaudeOcupacional\Regras\Condicoes;

use Illuminate\Validation\ValidationException;

/**
 * Condições disponíveis no editor de regras. Para criar uma nova, implemente CondicaoRegra e registre aqui.
 */
class CatalogoCondicoes
{
    /** @var array<string, CondicaoRegra> */
    private array $condicoes = [];

    public function __construct()
    {
        $todas = [
            new CondicaoIdadeMinima(),
            new CondicaoIdadeMaxima(),
            new CondicaoCargo(),
            new CondicaoExpostoRisco(),
            new CondicaoRisco(),
        ];

        foreach ($todas as $condicao) {
            $this->condicoes[$condicao->tipo()] = $condicao;
        }
    }

    public function obter(string $tipo): CondicaoRegra
    {
        if (!isset($this->condicoes[$tipo])) {
            throw ValidationException::withMessages(['condicoes' => 'Condição desconhecida: ' . $tipo . '.']);
        }

        return $this->condicoes[$tipo];
    }

    /**
     * @return list<array{tipo: string, rotulo: string}>
     */
    public function listar(): array
    {
        return array_values(array_map(
            fn (CondicaoRegra $c) => ['tipo' => $c->tipo(), 'rotulo' => $c->rotulo()],
            $this->condicoes
        ));
    }

    /**
     * Valida a lista de condições: ao menos uma, no máximo uma de cada tipo.
     *
     * @param  mixed  $condicoes
     * @return list<array{tipo: string, valor: mixed}>
     */
    public function normalizar(mixed $condicoes): array
    {
        if (!is_array($condicoes) || $condicoes === []) {
            throw ValidationException::withMessages(['condicoes' => 'Informe pelo menos uma condição (ex.: idade ou cargo).']);
        }

        $normalizadas = [];
        foreach ($condicoes as $item) {
            $tipo = (string) ($item['tipo'] ?? '');
            if (isset($normalizadas[$tipo])) {
                throw ValidationException::withMessages(['condicoes' => 'Cada condição pode aparecer só uma vez na regra.']);
            }
            $normalizadas[$tipo] = [
                'tipo' => $tipo,
                'valor' => $this->obter($tipo)->normalizar($item['valor'] ?? null),
            ];
        }

        $minima = $normalizadas[CondicaoIdadeMinima::TIPO]['valor'] ?? null;
        $maxima = $normalizadas[CondicaoIdadeMaxima::TIPO]['valor'] ?? null;
        if ($minima !== null && $maxima !== null && $minima > $maxima) {
            throw ValidationException::withMessages(['condicoes' => 'A idade mínima não pode ser maior que a máxima.']);
        }

        return array_values($normalizadas);
    }
}
