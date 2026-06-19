<?php

namespace App\Data\IntegracaoSpa;

use App\Models\VagasAbertas;

final class VagaSpaDetailData
{
    private function __construct(
        private readonly VagaSpaListItem $item,
    ) {
    }

    public static function fromVagasAbertas(VagasAbertas $vaga): self
    {
        return new self(VagaSpaListItem::fromVagasAbertas($vaga));
    }

    public function toArray(): array
    {
        return $this->item->toArray();
    }
}
