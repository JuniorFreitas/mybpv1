<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPasswordReset
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Verifica se o usuário está autenticado
        if (Auth::check()) {
            $user = Auth::user();
            
            // Verifica se precisa alterar a senha
            if ($user->needsPasswordReset()) {
                // Se a requisição for para a página de alteração de senha ou logout, permite
                if ($request->routeIs('alterar-senha.*') || $request->routeIs('logout') || $request->routeIs('sair')) {
                    return $next($request);
                }
                
                $reason = $user->getPasswordResetReason();
                
                // Se for requisição AJAX, retorna erro JSON
                if ($request->expectsJson()) {
                    return response()->json([
                        'msg' => $reason,
                        'require_password_reset' => true,
                        'first_access' => $user->isFirstAccess(),
                        'temporary_password' => $user->hasTemporaryPassword()
                    ], 403);
                }
                
                // Redireciona para a página de alteração de senha
                return redirect()->route('alterar-senha.index')
                    ->with('warning', $reason);
            }
        }
        
        return $next($request);
    }
}
