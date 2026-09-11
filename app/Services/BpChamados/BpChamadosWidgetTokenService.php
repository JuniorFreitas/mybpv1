<?php

namespace App\Services\BpChamados;

use App\Models\Cliente;
use App\Models\User;
use InvalidArgumentException;
use RuntimeException;

/**
 * Mint de JWT HS256 para o widget BP Chamados (portal).
 *
 * @see tickets/packages/widget/docs/integration.md
 */
class BpChamadosWidgetTokenService
{
    public function isConfigured(): bool
    {
        return filled(config('services.bp_chamados.api_base_url'))
            && filled(config('services.bp_chamados.application_id'))
            && filled(config('services.bp_chamados.widget_secret'));
    }

    public function isEnabled(): bool
    {
        return (bool) config('services.bp_chamados.enabled') && $this->isConfigured();
    }

    /**
     * @return array{token: string, expires_in: int}
     */
    public function mintFor(User $user): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Integração BP Chamados não configurada.');
        }

        $ttl = max(60, (int) config('services.bp_chamados.token_ttl', 900));
        $now = time();

        $claims = [
            'iss' => (string) config('services.bp_chamados.application_id'),
            'sub' => (string) $user->id,
            'name' => (string) $user->nome,
            'email' => $user->login ?: null,
            'iat' => $now,
            'exp' => $now + $ttl,
        ];

        $company = $this->companyClaimFor($user);
        if ($company !== null) {
            $claims['company'] = $company;
        }

        return [
            'token' => $this->encode($claims, (string) config('services.bp_chamados.widget_secret')),
            'expires_in' => $ttl,
        ];
    }

    /**
     * @return array{id: string, name: string|null, alias: string|null}|null
     */
    public function companyClaimFor(User $user): ?array
    {
        $empresa = $user->relationLoaded('Empresa')
            ? $user->Empresa
            : $user->Empresa()->first();

        if (! $empresa instanceof Cliente) {
            return null;
        }

        $name = $empresa->nome_fantasia ?: $empresa->razao_social;

        return [
            'id' => (string) $empresa->id,
            'name' => $name !== null && $name !== '' ? (string) $name : null,
            'alias' => filled($empresa->apelido) ? (string) $empresa->apelido : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    private function encode(array $claims, string $secret): string
    {
        if ($secret === '') {
            throw new InvalidArgumentException('Widget secret vazio.');
        }

        $header = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $payload = $this->base64UrlEncode(json_encode($claims, JSON_THROW_ON_ERROR));
        $signature = $this->base64UrlEncode(hash_hmac('sha256', "{$header}.{$payload}", $secret, true));

        return "{$header}.{$payload}.{$signature}";
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
