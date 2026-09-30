<?php

namespace Tests\Feature\Authorization;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CihRotaAutorizacaoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'can:admissao_cih'])
            ->get('/__test/cih-pilot', static fn () => response('ok', 200));
    }

    public function test_rota_cih_retorna_403_sem_habilidade(): void
    {
        Gate::define('admissao_cih', static fn () => false);

        $user = new \App\Models\User();
        $user->id = 1;
        $this->actingAs($user);

        $this->get('/__test/cih-pilot')->assertForbidden();
    }

    public function test_rota_cih_permite_com_habilidade(): void
    {
        Gate::define('admissao_cih', static fn () => true);

        $user = new \App\Models\User();
        $user->id = 1;
        $this->actingAs($user);

        $this->get('/__test/cih-pilot')->assertOk();
    }
}
