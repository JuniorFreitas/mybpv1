<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autoriza se o usuário tiver ao menos uma das habilidades (OR).
 * Uso: middleware('can.any:hab1,hab2,hab3')
 */
class CanAnyHabilidade
{
    public function handle(Request $request, Closure $next, string ...$habilidades): Response
    {
        $user = $request->user();
        if ($user === null) {
            abort(403);
        }

        foreach ($habilidades as $habilidade) {
            foreach (explode(',', $habilidade) as $nome) {
                $nome = trim($nome);
                if ($nome !== '' && $user->can($nome)) {
                    return $next($request);
                }
            }
        }

        abort(403);
    }
}
