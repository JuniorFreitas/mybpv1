<?php

namespace App\Http\Middleware;

use App\Models\Habilidade;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CarregaHabilidades
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user()->ativo) {
            return redirect()->route('logout');
        }

        $listaDeHabilidadesSistema = \Cache::get('habilidades_sistema');

        if (is_null(\Cache::get('habilidades_sistema'))) {
            $listaDeHabilidadesSistema = Habilidade::select('nome')->pluck('nome')->toArray();
            \Cache::rememberForever('habilidades_sistema', function () use ($listaDeHabilidadesSistema) {
                return $listaDeHabilidadesSistema;
            });
        }

        foreach ($listaDeHabilidadesSistema as $habilidade) {
            Gate::define($habilidade, function ($listaDeHabilidadesUsuario) use ($habilidade) {
                if (collect($listaDeHabilidadesUsuario)->search($habilidade) !== false) {
                    return true;
                }
                return false;
            });
        }

        return $next($request);
    }
}
