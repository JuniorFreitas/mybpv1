<?php

namespace App\Http\Middleware;

use App\Models\Cliente;
use Closure;
use Illuminate\Http\Request;

class ApiToken
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string  ...$requiredScopes
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next, string ...$requiredScopes)
    {
        $header = (string) $request->header('X-API-TOKEN', '');
        $credencial = $this->resolveCredential($header);
        if ($credencial === null) {
            return response()->json(['msg' => 'Não autorizado', 'success' => false], 403);
        }

        foreach ($requiredScopes as $scope) {
            if ($scope !== '' && !in_array($scope, $credencial['scopes'], true)) {
                return response()->json(['msg' => 'Escopo insuficiente', 'success' => false], 403);
            }
        }

        $request->attributes->set('api_integracao', $credencial);

        $empresaId = $this->empresaIdDoRequest($request);
        if ($empresaId !== null && !$this->permiteEmpresa($credencial, $empresaId)) {
            return response()->json(['msg' => 'Empresa não autorizada para este token', 'success' => false], 403);
        }

        return $next($request);
    }

    /**
     * @return array{scopes: string[], empresa_ids: int[]}|null
     */
    private function resolveCredential(string $header): ?array
    {
        if ($header === '') {
            return null;
        }

        $tokens = config('api_integracao.tokens', []);
        if (!is_array($tokens)) {
            return null;
        }

        foreach ($tokens as $tokenValue => $entry) {
            if ($tokenValue === '' || !is_string($tokenValue)) {
                continue;
            }
            if (!hash_equals((string) $tokenValue, $header)) {
                continue;
            }

            return [
                'scopes' => array_values($entry['scopes'] ?? []),
                'empresa_ids' => array_values($entry['empresa_ids'] ?? []),
            ];
        }

        return null;
    }

    private function permiteEmpresa(array $credencial, int $empresaId): bool
    {
        $allow = $credencial['empresa_ids'] ?? [];
        if ($allow === []) {
            return true;
        }

        return in_array($empresaId, $allow, true);
    }

    private function empresaIdDoRequest(Request $request): ?int
    {
        if ($request->filled('empresa_id')) {
            return (int) $request->input('empresa_id');
        }

        $apelido = $request->route('apelido') ?? $request->route('empresa_slug');
        if ($apelido) {
            $cliente = Cliente::withoutGlobalScopes()
                ->where('apelido', $apelido)
                ->where('ativo', true)
                ->first(['id']);

            return $cliente ? (int) $cliente->id : null;
        }

        return null;
    }
}
