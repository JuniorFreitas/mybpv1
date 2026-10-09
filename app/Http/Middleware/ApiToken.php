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
        $expected = (string) config('services.api.token', '');
        $provided = (string) $request->header('X-API-TOKEN', '');

        if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
            return response()->json(['msg' => 'Não autorizado', 'success' => false], 403);
        }

        return $next($request);
    }
}
