<?php

namespace Tests\Unit\Services\Cih;

use App\Models\Cih;
use App\Services\Cih\CihFilterApplier;
use Tests\TestCase;

class CihFilterApplierStatusTest extends TestCase
{
    private function apply(string $status): \Illuminate\Database\Eloquent\Builder
    {
        $query = Cih::query();
        (new CihFilterApplier([
            'campoStatusAprovacao' => $status,
        ]))->apply($query);

        return $query;
    }

    public function test_aprovado_gestor_nao_e_status_atual(): void
    {
        $query = $this->apply('aprovado_gestor');

        $this->assertStringContainsString('1 = 0', $query->toSql());
        $this->assertNotContains('aprovado', $query->getBindings());
    }

    public function test_pendente_rh_exige_gestor_aprovado_e_rh_vazio(): void
    {
        $query = $this->apply('pendente_rh');

        $this->assertStringContainsString('status', $query->toSql());
        $this->assertStringContainsString('resposta_rh', $query->toSql());
        $this->assertContains('aprovado', $query->getBindings());
    }

    public function test_pendente_gestor_exige_status_aberto(): void
    {
        $query = $this->apply('pendente_gestor');

        $this->assertContains('aberto', $query->getBindings());
        $this->assertStringContainsString('resposta_rh', $query->toSql());
    }
}
