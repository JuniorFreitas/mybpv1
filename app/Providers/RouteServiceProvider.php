<?php

namespace App\Providers;

use App\Models\RequisicaoVagaMovimentacao;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/g/dashboard';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        Route::bind('requisicaoVaga', function ($value) {
            return RequisicaoVagaMovimentacao::findOrFail($value);
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('api-externa-token', function (Request $request) {
            $perMinute = (int) config('services.api_externa.token_throttle_per_minute', 10);
            $clientId = (string) $request->input('client_id', '');

            return Limit::perMinute($perMinute)->by('api-externa-token:' . ($clientId !== '' ? $clientId : $request->ip()));
        });

        RateLimiter::for('api-externa', function (Request $request) {
            $perMinute = (int) config('services.api_externa.list_throttle_per_minute', 120);

            return Limit::perMinute($perMinute)->by(
                'api-externa:' . (optional($request->user())->id ?: $request->ip())
            );
        });

        RateLimiter::for('ai-vaga-descricao', function (Request $request) {
            $perMinute = app()->environment('local') ? 60 : 10;

            return Limit::perMinute($perMinute)->by(optional($request->user())->id ?: $request->ip());
        });

        // Limita a leitura de seções para o manual não ser copiado inteiro, seção por seção.
        RateLimiter::for('assistente-manual-secao', function (Request $request) {
            return Limit::perMinute(20)->by(optional($request->user())->id ?: $request->ip());
        });

        // Três travas: ritmo por usuário, teto diário por usuário e teto diário do sistema inteiro,
        // para que ninguém (nem um script com sessão válida) esgote a cota gratuita do provedor de IA.
        RateLimiter::for('assistente-manual', function (Request $request) {
            $perMinute = app()->environment('local') ? 60 : 8;
            $usuario = optional($request->user())->id ?: $request->ip();

            return [
                Limit::perMinute($perMinute)->by('assistente:min:'.$usuario),
                Limit::perDay((int) config('services.assistente_manual.limite_diario_usuario', 80))->by('assistente:dia:'.$usuario),
                Limit::perDay((int) config('services.assistente_manual.limite_diario_global', 600))->by('assistente:dia:global'),
            ];
        });
    }
}
