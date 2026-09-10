<?php

namespace App\Infrastructure\Ai;

use App\Domain\Ai\LlmClient;
use App\Domain\Ai\RateLimitedException;
use App\Domain\Support\SafeOutboundUrl;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class GeminiHttpClient implements LlmClient
{
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    private const TIMEOUT_SECONDS = 20;

    public function __construct(
        private readonly SafeOutboundUrl $safeOutboundUrl,
    ) {
    }

    public function isConfigured(): bool
    {
        return filled(config('services.gemini.api_key'));
    }

    public function generate(string $modelId, string $system, string $prompt): string
    {
        $apiKey = config('services.gemini.api_key');
        if (blank($apiKey)) {
            throw new RuntimeException('Gemini não está configurado.');
        }

        $url = self::BASE_URL.'/'.$modelId.':generateContent';

        $allowedHosts = config('services.gemini.allowed_hosts', []);
        if (! $this->safeOutboundUrl->isAllowed($url, $allowedHosts, requireHttps: true, resolveDns: app()->isProduction())) {
            Log::warning('ai.gemini.blocked_url', ['model_id' => $modelId]);
            throw new RuntimeException('URL do provedor Gemini bloqueada pela política de saída.');
        }

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
                ->timeout(self::TIMEOUT_SECONDS)
                ->post($url, [
                    'system_instruction' => [
                        'parts' => [['text' => $system]],
                    ],
                    'contents' => [
                        ['role' => 'user', 'parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        // Folga acima de ~1200 palavras em PT-BR (o uso atual mais longo) para não truncar o HTML no meio de uma tag.
                        'maxOutputTokens' => 2048,
                    ],
                ]);
        } catch (Throwable $exception) {
            Log::warning('ai.gemini.failed', ['model_id' => $modelId, 'error' => $exception->getMessage()]);
            throw new RuntimeException('Falha de transporte ao chamar o Gemini.', previous: $exception);
        }

        if ($response->status() === 429) {
            Log::warning('ai.gemini.rate_limited', ['model_id' => $modelId]);
            throw new RateLimitedException('O Gemini aplicou limite de taxa.');
        }

        if ($response->failed()) {
            Log::warning('ai.gemini.rejected', ['model_id' => $modelId, 'status' => $response->status()]);
            throw new RuntimeException('O Gemini retornou um status sem sucesso.');
        }

        $text = $response->json('candidates.0.content.parts.0.text');
        if (blank($text)) {
            Log::warning('ai.gemini.rejected', ['model_id' => $modelId, 'status' => $response->status(), 'reason' => 'empty_text']);
            throw new RuntimeException('O Gemini não retornou texto.');
        }

        Log::info('ai.gemini.ok', ['model_id' => $modelId]);

        return $text;
    }
}
