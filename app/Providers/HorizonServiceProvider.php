<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function boot()
    {
        parent::boot();
    }

    protected function authorization()
    {
        Horizon::auth(function ($request) {
            $user = $request->user();
            if (!$user) {
                return false;
            }

            if (Gate::forUser($user)->allows('viewHorizon')) {
                return true;
            }

            // Operador MyBP com papel plataforma (grupo_id=1)
            return (int) $user->empresa_id === User::MYBP_EMPRESA_ID
                && (int) $user->grupo_id === 1;
        });
    }

    protected function gate()
    {
        Gate::define('viewHorizon', function ($user) {
            $allow = config('horizon.allowed_logins', []);

            return in_array($user->login, $allow, true);
        });
    }
}
