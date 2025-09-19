<?php

namespace App\Http\Middleware;

use App\Models\Habilidade;
use App\Models\Papel;
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
        if (!auth()->user()->ativo || !$this->verificaSeGrupoEstaAtivo()) {
            return redirect()->route('logout');
        }
        $listaDeHabilidadesSistema = Habilidade::select('nome')->pluck('nome')->toArray();

        foreach ($listaDeHabilidadesSistema as $habilidade) {
            Gate::define($habilidade, function (User $usuario) use ($habilidade) {
                if (collect($usuario->listaDeHabilidades())->search($habilidade) !== false) {
                    return true;
                }
                return false;
            });
        }

        return $next($request);
    }

    private function verificaSeGrupoEstaAtivo()
    {
        return (bool)Papel::whereId(auth()->user()->grupo_id)->where('ativo', true)->first();
    }
}
