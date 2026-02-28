<?php

namespace App\Http\Middleware;

use App\Models\Sistema;
use Closure;
use Illuminate\Http\Request;

class VerificaAssinaturaDigitalHabilitada
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        if (Sistema::assinaturaDigitalHabilitada((int) auth()->user()->empresa_id)) {
            return $next($request);
        }

        $mensagem = 'Assinatura digital não está habilitada para esta empresa.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $mensagem,
            ], 403);
        }

        abort(403, $mensagem);
    }
}

