<?php

namespace App\Services\ApiExterna;

use App\Models\EmpresaApiCredencial;
use App\Models\User;
use App\Support\ApiExterna\ApiExternaScopes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class ApiExternaCredencialService
{
    /**
     * Cria ou rotaciona credenciais. Retorna secret em claro apenas nesta chamada.
     *
     * @return array{client_id: string, client_secret: string, empresa_id: int, user_id: int, scopes: array}
     */
    public function criarOuRotacionar(int $empresaId, bool $rotate = false, ?array $scopes = null): array
    {
        $scopes = $scopes ?: ApiExternaScopes::all();
        $this->assertScopesValidos($scopes);

        return DB::transaction(function () use ($empresaId, $rotate, $scopes) {
            $existente = EmpresaApiCredencial::query()
                ->where('empresa_id', $empresaId)
                ->whereNull('deleted_at')
                ->first();

            if ($existente && ! $rotate) {
                throw new RuntimeException(
                    'Já existe credencial para esta empresa. Use --rotate para gerar novo secret.'
                );
            }

            $user = $this->garantirUsuarioApi($empresaId);
            $clientSecret = Str::random(64);
            $clientId = $existente?->client_id ?: ('mybp_' . Str::lower(Str::random(32)));

            if ($existente) {
                $user->tokens()->delete();
                $existente->update([
                    'user_id' => $user->id,
                    'client_secret_hash' => Hash::make($clientSecret),
                    'scopes' => $scopes,
                    'ativo' => true,
                ]);
                $credencial = $existente->fresh();
            } else {
                $credencial = EmpresaApiCredencial::query()->create([
                    'empresa_id' => $empresaId,
                    'user_id' => $user->id,
                    'client_id' => $clientId,
                    'client_secret_hash' => Hash::make($clientSecret),
                    'scopes' => $scopes,
                    'ativo' => true,
                ]);
            }

            return [
                'client_id' => $credencial->client_id,
                'client_secret' => $clientSecret,
                'empresa_id' => $empresaId,
                'user_id' => $user->id,
                'scopes' => $credencial->scopes,
            ];
        });
    }

    public function autenticar(string $clientId, string $clientSecret): ?EmpresaApiCredencial
    {
        $credencial = EmpresaApiCredencial::query()
            ->where('client_id', $clientId)
            ->where('ativo', true)
            ->whereNull('deleted_at')
            ->first();

        if (! $credencial || ! Hash::check($clientSecret, $credencial->client_secret_hash)) {
            return null;
        }

        $user = User::query()
            ->where('id', $credencial->user_id)
            ->where('tipo', User::API)
            ->where('ativo', true)
            ->whereNull('deleted_at')
            ->first();

        if (! $user || (int) $user->empresa_id !== (int) $credencial->empresa_id) {
            return null;
        }

        $credencial->setRelation('user', $user);

        return $credencial;
    }

    private function garantirUsuarioApi(int $empresaId): User
    {
        $login = 'api.empresa.' . $empresaId;

        $user = User::withTrashed()
            ->where('empresa_id', $empresaId)
            ->where('tipo', User::API)
            ->first();

        if ($user) {
            if ($user->trashed()) {
                $user->restore();
            }
            $user->update([
                'ativo' => true,
                'nome' => 'API Empresa ' . $empresaId,
                'login' => $login,
                'password' => Hash::make(Str::random(40)),
            ]);

            return $user->fresh();
        }

        return User::query()->create([
            'nome' => 'API Empresa ' . $empresaId,
            'login' => $login,
            'password' => Hash::make(Str::random(40)),
            'tipo' => User::API,
            'empresa_id' => $empresaId,
            'ativo' => true,
            'temp' => false,
            'termos' => true,
            'api_token' => (string) Str::uuid(),
            'require_password_reset' => false,
            'password_reset_days' => null,
        ]);
    }

    private function assertScopesValidos(array $scopes): void
    {
        $invalidos = array_diff($scopes, ApiExternaScopes::all());
        if ($invalidos !== []) {
            throw new RuntimeException('Scopes inválidos: ' . implode(', ', $invalidos));
        }
    }
}
