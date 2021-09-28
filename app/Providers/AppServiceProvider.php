<?php

namespace App\Providers;

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
        if ($this->app->isProduction()) {
            \URL::forceScheme('https');
        }


        Schema::defaultStringLength(191);

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
