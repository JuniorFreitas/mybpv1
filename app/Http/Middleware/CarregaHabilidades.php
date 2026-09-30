<?php

namespace App\Http\Middleware;

use App\Authorization\HabilidadeImplication;
use App\Authorization\HabilidadeRegistry;
use App\Models\Habilidade;
use App\Models\Papel;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class CarregaHabilidades
{
    public const CACHE_KEY_NOMES = 'habilidades:nomes:v1';

    public const CACHE_TTL_SECONDS = 300;

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

        $registry = app(HabilidadeRegistry::class);
        $nomes = $this->nomesHabilidadesSistema();
        $nomesSet = array_fill_keys($nomes, true);

        foreach ($nomes as $habilidade) {
            Gate::define($habilidade, static function (User $usuario) use ($habilidade, $nomesSet): bool {
                return HabilidadeImplication::allows(
                    $habilidade,
                    $usuario->listaDeHabilidades(),
                    $nomesSet
                );
            });
        }

        foreach ($registry->aliasMap() as $alias => $canonico) {
            if (!isset($nomesSet[$canonico])) {
                continue;
            }
            Gate::define($alias, static function (User $usuario) use ($canonico, $nomesSet): bool {
                return HabilidadeImplication::allows(
                    $canonico,
                    $usuario->listaDeHabilidades(),
                    $nomesSet
                );
            });
        }

        return $next($request);
    }

    public static function forgetNomesCache(): void
    {
        Cache::forget(self::CACHE_KEY_NOMES);
    }

    /**
     * @return list<string>
     */
    private function nomesHabilidadesSistema(): array
    {
        return Cache::remember(self::CACHE_KEY_NOMES, self::CACHE_TTL_SECONDS, static function () {
            return Habilidade::query()->orderBy('nome')->pluck('nome')->all();
        });
    }

    private function verificaSeGrupoEstaAtivo()
    {
        return (bool) Papel::whereId(auth()->user()->grupo_id)->where('ativo', true)->first();
    }
}
