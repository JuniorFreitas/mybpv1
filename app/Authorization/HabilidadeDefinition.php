<?php

namespace App\Authorization;

/**
 * Value object imutável de uma habilidade do catálogo tipado.
 */
final class HabilidadeDefinition
{
    /**
     * @param list<string> $aliases
     */
    public function __construct(
        public readonly string $nome,
        public readonly string $modulo,
        public readonly string $recurso,
        public readonly string $acao,
        public readonly string $descricao,
        public readonly array $aliases = [],
        public readonly ?string $moduloLabel = null,
        public readonly ?string $recursoLabel = null,
        public readonly ?string $acaoLabel = null,
    ) {
    }

    /**
     * @return array{
     *     nome: string,
     *     modulo: string,
     *     recurso: string,
     *     acao: string,
     *     descricao: string,
     *     aliases: list<string>,
     *     modulo_label: string,
     *     recurso_label: string,
     *     acao_label: string
     * }
     */
    public function toMetaArray(): array
    {
        return [
            'nome' => $this->nome,
            'modulo' => $this->modulo,
            'recurso' => $this->recurso,
            'acao' => $this->acao,
            'descricao' => $this->descricao,
            'aliases' => $this->aliases,
            'modulo_label' => $this->moduloLabel ?? $this->labelize($this->modulo),
            'recurso_label' => $this->recursoLabel ?? $this->labelize($this->recurso),
            'acao_label' => $this->acaoLabel ?? $this->labelize($this->acao),
        ];
    }

    private function labelize(string $slug): string
    {
        $slug = str_replace(['-', '_'], ' ', $slug);

        return mb_convert_case($slug, MB_CASE_TITLE, 'UTF-8');
    }
}
