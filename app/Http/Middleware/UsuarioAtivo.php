<?php

namespace App\Http\Middleware;

use Closure;

class UsuarioAtivo
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // se nao tem a habilidade ou nem se quer tem o usuario, diz que nao esta autorizado

        if (auth()->user() && !auth()->user()->ativo) {
            auth()->user()->tokens()->delete();
            return response()->json([
                'msg' => 'Usuário desativado',
                'success' => false
            ], 401);
        }
        return $next($request);
    }
}
