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
