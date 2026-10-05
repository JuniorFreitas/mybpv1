<?php

namespace Tests\Unit\Services\FeriasPrevista;

use App\Models\Ferias;
use App\Models\User;
use App\Services\FeriasPrevista\FeriasPrevistaFilterApplier;
use Tests\TestCase;

class FeriasPrevistaFilterApplierTest extends TestCase
{
    public function test_aprovado_gestor_nao_e_status_atual_do_fluxo(): void
    {
        $query = Ferias::query();
        $user = new User();
        $user->id = 1;
        $user->empresa_id = 1;

        (new FeriasPrevistaFilterApplier([
            'campoStatusAprovacao' => 'aprovado_gestor',
            '_full_export_access' => true,
        ], $user))->apply($query);

        $this->assertStringContainsString('1 = 0', $query->toSql());
        $this->assertNotContains(Ferias::STATUS_APROVADO, $query->getBindings());
    }
}
