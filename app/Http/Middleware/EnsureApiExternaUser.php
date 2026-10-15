<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureApiExternaUser
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || $user->tipo !== User::API || ! $user->ativo) {
            return response()->json([
                'success' => false,
                'msg' => 'Não autorizado',
            ], 401);
        }

        return $next($request);
    }
}
