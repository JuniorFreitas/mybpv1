<?php

namespace App\Providers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \App\Contracts\IntegracaoSpa\EmpresaIntegracaoSpaQuery::class,
            \App\Services\IntegracaoSpa\EmpresaIntegracaoSpaEloquent::class
        );
        $this->app->bind(
            \App\Contracts\IntegracaoSpa\VagaIntegracaoSpaQuery::class,
            \App\Services\IntegracaoSpa\VagaIntegracaoSpaEloquent::class
        );
        $this->app->bind(\App\Domain\Ai\LlmClient::class, \App\Infrastructure\Ai\GeminiHttpClient::class);
        $this->app->bind(\App\Domain\Ai\LlmProxy::class, \App\Infrastructure\Ai\FallbackLlmProxy::class);
        $this->app->singleton(\App\Domain\Ajuda\BaseConhecimentoManual::class, \App\Infrastructure\Ajuda\BaseConhecimentoManualJsonl::class);

        if ($this->app->isLocal()) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Telas públicas de assinatura digital: cabeçalho com a logo da empresa da URL (/{apelido}/assinatura/...).
        \Illuminate\Support\Facades\View::composer('assinatura.layout', function ($view) {
            if (!$view->offsetExists('marca')) {
                $view->with('marca', app(\App\Services\Marca\MarcaClienteService::class)
                    ->dadosPorApelido(request()->segment(1)));
            }
        });

        if ($this->app->isProduction() || env('MIX_AMBIENTE') == 'prod') {
            \URL::forceScheme('https');
        }

        if ($this->app->environment('local')) {
            $redirectTo = config('mail.local_redirect_to');
            if (is_string($redirectTo)) {
                $redirectTo = trim($redirectTo);
            }
            if (is_string($redirectTo) && $redirectTo !== '') {
                Mail::alwaysTo($redirectTo);
            }
        }

        Schema::defaultStringLength(250);

        // ✅ Observer para invalidar cache de RH automaticamente
        \App\Models\User::observe(\App\Observers\UserRHCacheObserver::class);

        \App\Models\Cliente::observe(\App\Observers\ClienteIntegracaoSpaEmpresasAtivasCacheObserver::class);
        \App\Models\Arquivo::observe(\App\Observers\ArquivoLogotipoIntegracaoSpaEmpresasAtivasCacheObserver::class);
        \App\Models\VagasAbertas::observe(\App\Observers\VagasAbertasIntegracaoSpaPaginaCacheObserver::class);

        \Route::resourceVerbs([
            'create' => 'novo',
            'edit' => 'editar',
            'update' => 'atualizar',
            'store' => 'cadastrar',
            'show' => 'exibir',
            'destroy' => 'apagar'
        ]);
    }
}
