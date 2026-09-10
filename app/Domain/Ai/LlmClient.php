<?php

namespace App\Domain\Ai;

interface LlmClient
{
    public function isConfigured(): bool;

    public function generate(
        string $modelId,
        string $system,
        string $prompt,
    ): string;
}
