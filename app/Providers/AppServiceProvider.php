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
