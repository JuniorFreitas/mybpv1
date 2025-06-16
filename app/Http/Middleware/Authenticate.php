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
            $user = \Auth::user();
            
            // Usuário desativado ou candidato
            if ($user->ativo == 0 || $user->tipo == User::CANDIDATO) {
                \Auth::logout();
                return redirect()->route('login')->with('error', 'Sua conta não está ativa.');
            }
            
            // Não bloqueia aqui se tem senha temporária - deixa o CheckPasswordReset lidar com isso
            // Remove a verificação de temp == 1 para permitir que o usuário entre e seja redirecionado
            
            return $next($request);
        }
        return redirect()->route('login');
    }
}
