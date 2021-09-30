<?php

namespace App\Http\Middleware;

use Closure;

class TemHabilidade
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$habilidade)
    {
        // se nao tem a habilidade ou nem se quer tem o usuario, diz que nao esta autorizado
        if(!auth()->user()->tokenCan($habilidade) || !auth()->user()) {
            return response()->json(['msg'=>'Não autorizado', 'success' => false],403);
        }
        return $next($request);
    }
}
