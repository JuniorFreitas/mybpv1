<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerificarPontoEletronico {
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        if (auth()->user()->EscalasFuncionario()->count() && auth()->user()->can('ponto-eletronico')) {
            if ($request->fullUrl() != route('g.controle-ponto.ponto-eletronico.index')) {
                //return \Redirect::to(route('g.controle-ponto.ponto-eletronico.index'));
                //return route('g.controle-ponto.ponto-eletronico.index');
            }

        }

        return $next($request);
    }
}
