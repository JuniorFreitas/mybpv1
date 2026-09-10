<?php

namespace App\Infrastructure\Ai;

use App\Domain\Ai\GeminiModelCatalog;
use App\Domain\Ai\LlmClient;
use App\Domain\Ai\LlmProxy;
use App\Domain\Ai\RateLimitedException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Distribui requisições entre os modelos configurados em ordem aleatória e
 * tenta o próximo modelo da lista quando um deles falha (fallback).
 *
 * Os modelos aqui compartilham a mesma chave/cota do provedor: um rate limit
 * em um modelo tende a valer para os outros também. Por isso, um 429 não
 * dispara fallback para o próximo modelo (isso só multiplicaria as chamadas
 * contra a mesma cota) — em vez disso, interrompe a tentativa e coloca o
 * provedor em cooldown, para que as próximas requisições falhem rápido sem
 * bater no Gemini enquanto ele estiver limitando.
 */
final class FallbackLlmProxy implements LlmProxy
{
    private const COOLDOWN_CACHE_KEY = 'ai:health:gemini:cooldown_until';

    private const COOLDOWN_SECONDS = 60;

    public function __construct(
        private readonly LlmClient $client,
        private readonly GeminiModelCatalog $catalog,
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->client->isConfigured() && $this->models() !== [];
    }

    public function generate(string $system, string $prompt): array
    {
        $models = $this->models();
        if ($models === []) {
            throw new RuntimeException('Nenhum modelo Gemini configurado.');
        }

        if ($this->emCooldown()) {
            throw new RuntimeException('Gemini em limite de taxa. Aguarde antes de tentar novamente.');
        }

        $lastException = null;
        foreach ($this->emOrdemAleatoria($models) as $model) {
            try {
                $text = $this->client->generate($model['id'], $system, $prompt);

                return [
                    'text' => $text,
                    'model' => $model['alias'],
                    'model_id' => $model['id'],
                ];
            } catch (RateLimitedException $exception) {
                Log::info('ai.proxy.model_failed', ['model_id' => $model['id'], 'error' => $exception->getMessage()]);
                $this->iniciarCooldown();

                throw new RuntimeException('Gemini em limite de taxa. Aguarde antes de tentar novamente.', previous: $exception);
            } catch (RuntimeException $exception) {
                $lastException = $exception;
                Log::info('ai.proxy.model_failed', ['model_id' => $model['id'], 'error' => $exception->getMessage()]);
            }
        }

        throw new RuntimeException('Nenhum modelo Gemini respondeu.', previous: $lastException);
    }

    private function emCooldown(): bool
    {
        $until = Cache::get(self::COOLDOWN_CACHE_KEY);

        return $until !== null && now()->lt($until);
    }

    private function iniciarCooldown(): void
    {
        Cache::put(self::COOLDOWN_CACHE_KEY, now()->addSeconds(self::COOLDOWN_SECONDS), self::COOLDOWN_SECONDS);
    }

    /**
     * @return array<int, array{alias: string, id: string}>
     */
    private function models(): array
    {
        return $this->catalog->resolve(
            config('services.gemini.models'),
            config('services.gemini.model'),
        );
    }

    /**
     * @param  array<int, array{alias: string, id: string}>  $models
     * @return array<int, array{alias: string, id: string}>
     */
    private function emOrdemAleatoria(array $models): array
    {
        shuffle($models);

        return $models;
    }
}
