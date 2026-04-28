<?php

namespace App\Services\Cbo;

final class CboImportResult
{
    public function __construct(
        public readonly int $familiasProcessadas = 0,
        public readonly int $familiasIgnoradas = 0,
        public readonly int $ocupacoesProcessadas = 0,
        public readonly int $ocupacoesIgnoradas = 0,
        public readonly int $perfisAtualizados = 0,
        public readonly int $perfisIgnorados = 0,
    ) {}
}
