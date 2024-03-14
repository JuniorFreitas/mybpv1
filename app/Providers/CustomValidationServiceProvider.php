<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class CustomValidationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('tel_principal_mark', function ($attribute, $value, $parameters, $validator) {
            foreach ($value as $telefone) {
                if (isset($telefone['principal']) && ($telefone['principal'] === true || $telefone['principal'] === 1)) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
