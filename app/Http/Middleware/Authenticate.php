<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
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
        if (!$request->expectsJson()) {
            return route('login');
        }
    }

    public function handle($request, Closure $next, ...$guards)
    {
        if (\Auth::guard()->check()) {
            if (\Auth::user()->temp == 1 || \Auth::user()->ativo == 0 || \Auth::user()->tipo == User::CANDIDATO) {
                \Auth::logout();
                return redirect()->route('login')->with('error', 'Sua conta não está ativa.');
            }
            return $next($request);
        }
        return redirect()->route('login');
    }
}
