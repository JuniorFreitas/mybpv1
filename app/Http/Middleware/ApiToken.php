<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiToken
{

    /**
     * @param Request $request
     * @param Closure $next
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next)
    {

        if (!$request->hasHeader('X-API-TOKEN') || $request->header('X-API-TOKEN') !== env('X_API_TOKEN')) {
            return response()->json(['msg' => 'Não autorizado', 'success' => false], 403);
        }

        return $next($request);
    }
}
