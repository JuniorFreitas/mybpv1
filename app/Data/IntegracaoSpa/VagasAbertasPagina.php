<?php

namespace App\Data\IntegracaoSpa;

final class VagasAbertasPagina
{
    /**
     * @param list<VagaSpaListItem> $itens
     */
    public function __construct(
        public readonly array $itens,
        public readonly int $currentPage,
        public readonly int $lastPage,
        public readonly int $perPage,
        public readonly int $total,
    ) {
    }

    public function toArray(): array
    {
        return [
            'itens' => array_map(static fn (VagaSpaListItem $item) => $item->toArray(), $this->itens),
            'paginacao' => [
                'pagina_atual' => $this->currentPage,
                'ultima_pagina' => $this->lastPage,
                'por_pagina' => $this->perPage,
                'total' => $this->total,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromSpaCacheArray(array $data): self
    {
        $pag = $data['paginacao'] ?? [];
        if (! is_array($pag)) {
            $pag = [];
        }

        $rawItens = $data['itens'] ?? [];
        $itens = [];
        if (is_array($rawItens)) {
            foreach ($rawItens as $row) {
                if (is_array($row)) {
                    $itens[] = VagaSpaListItem::fromSpaCacheArray($row);
                }
            }
        }

        return new self(
            itens: $itens,
            currentPage: (int) ($pag['pagina_atual'] ?? 1),
            lastPage: (int) ($pag['ultima_pagina'] ?? 1),
            perPage: (int) ($pag['por_pagina'] ?? 50),
            total: (int) ($pag['total'] ?? 0),
        );
    }
}
