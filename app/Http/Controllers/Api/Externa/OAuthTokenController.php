<?php

namespace App\Http\Controllers\Api\Externa;

use App\Http\Controllers\Controller;
use App\Services\ApiExterna\ApiExternaCredencialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OAuthTokenController extends Controller
{
    public function __construct(
        private readonly ApiExternaCredencialService $credenciais
    ) {
    }

    public function token(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'grant_type' => 'required|in:client_credentials',
            'client_id' => 'required|string|max:64',
            'client_secret' => 'required|string|max:255',
        ]);

        $credencial = $this->credenciais->autenticar(
            $validated['client_id'],
            $validated['client_secret']
        );

        if (! $credencial) {
            Log::warning('api_externa.token_negado', [
                'client_id' => $validated['client_id'],
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'msg' => 'Credenciais inválidas',
            ], 401);
        }

        $ttl = (int) config('services.api_externa.token_ttl_minutes', 60);
        $user = $credencial->user;
        $scopes = is_array($credencial->scopes) ? $credencial->scopes : [];

        $accessToken = $user->createToken(
            'api-externa',
            $scopes,
            now()->addMinutes($ttl)
        );

        $credencial->forceFill(['last_used_at' => now()])->save();

        return response()->json([
            'success' => true,
            'access_token' => $accessToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => $ttl * 60,
            'scope' => implode(' ', $scopes),
        ]);
    }
}
