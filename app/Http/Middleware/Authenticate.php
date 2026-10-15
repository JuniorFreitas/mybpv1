<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }

    public function handle($request, \Closure $next, ...$guards)
    {
        $isApi = $request->is('api/*') || $request->expectsJson();

        try {
            $this->authenticate($request, $guards);
        } catch (AuthenticationException $e) {
            if ($isApi) {
                return response()->json([
                    'msg' => 'Não autorizado',
                    'success' => false,
                ], 401);
            }

            return redirect()->route('login');
        }

        $user = $request->user() ?? auth()->user();

        if ($user && ((int) $user->ativo === 0 || $user->tipo == User::CANDIDATO)) {
            if ($isApi) {
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

                return response()->json([
                    'msg' => 'Não autorizado',
                    'success' => false,
                ], 401);
            }

            auth()->logout();

            return redirect()->route('login')->with('error', 'Sua conta não está ativa.');
        }

        return $next($request);
    }
}
