<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureApiExternaScope
{
    public function handle(Request $request, Closure $next, string $scope)
    {
        $user = $request->user();

        if (! $user || ! $user->tokenCan($scope)) {
            return response()->json([
                'success' => false,
                'msg' => 'Escopo insuficiente',
            ], 403);
        }

        return $next($request);
    }
}
