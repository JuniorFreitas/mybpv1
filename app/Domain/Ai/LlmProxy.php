<?php

namespace App\Domain\Ai;

interface LlmProxy
{
    public function isConfigured(): bool;

    /** @return array{text: string, model: string, model_id: string} */
    public function generate(string $system, string $prompt): array;
}
