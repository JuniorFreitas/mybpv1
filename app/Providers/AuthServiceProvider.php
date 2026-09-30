<?php

namespace App\Providers;

use App\Models\ItensCloud;
use App\Policies\HabilidadePolicy;
use App\Policies\ItensCloudPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        ItensCloud::class => ItensCloudPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // authorize('habilidade', 'nome_da_skill') — wrapper tipado do resolver
        Gate::define('habilidade', function ($user, string $nome) {
            return app(HabilidadePolicy::class)->access($user, $nome);
        });
    }
}
