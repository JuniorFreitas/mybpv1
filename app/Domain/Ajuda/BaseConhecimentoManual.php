<?php

namespace App\Domain\Ajuda;

interface BaseConhecimentoManual
{
    public function disponivel(): bool;

    /**
     * Identifica o conteúdo atual da base; muda sempre que o manual é regenerado.
     */
    public function versao(): string;

    /**
     * @return array<int, ManualTrecho>
     */
    public function trechos(): array;
}
