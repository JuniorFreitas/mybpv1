<?php

namespace App\Http\Middleware;

use App\Domain\SaudeOcupacional\ModuloSaudeOcupacional;
use Closure;
use Illuminate\Http\Request;

class VerificaModuloSaudeOcupacional
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && ModuloSaudeOcupacional::habilitado((int) auth()->user()->empresa_id)) {
            return $next($request);
        }

        $mensagem = 'O módulo Saúde Ocupacional não está habilitado para esta empresa.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $mensagem], 403);
        }

        abort(403, $mensagem);
    }
}
