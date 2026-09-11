<?php

namespace Tests\Unit\Services\TransferenciaPrevista;

use App\Models\TransferenciaPrevista;
use App\Models\User;
use App\Services\TransferenciaPrevista\TransferenciaPrevistaFilterApplier;
use Tests\TestCase;

class TransferenciaPrevistaFilterApplierTest extends TestCase
{
    private function userStub(): User
    {
        $user = new User();
        $user->id = 1;

        return $user;
    }

    public function test_filtro_aberto_exige_sem_rh_final_e_sem_reprovacao_em_qualquer_etapa(): void
    {
        $query = TransferenciaPrevista::query();
        (new TransferenciaPrevistaFilterApplier([
            'campoStatus' => 'aberto',
            '_full_export_access' => true,
        ], $this->userStub()))->apply($query);

        $sql = $query->toSql();

        $this->assertStringContainsString('resposta_rh', $sql);
        $this->assertStringContainsString('status_aprovacao_gestor_destino', $sql);
        $this->assertStringContainsString('status_aprovacao_gestor_unico', $sql);
        $this->assertStringContainsString('status_aprovacao_extra', $sql);
        $this->assertContains('reprovado', $query->getBindings());
    }

    public function test_filtro_aprovado_usa_resposta_rh(): void
    {
        $query = TransferenciaPrevista::query();
        (new TransferenciaPrevistaFilterApplier([
            'campoStatus' => 'aprovado',
            '_full_export_access' => true,
        ], $this->userStub()))->apply($query);

        $this->assertStringContainsString('"resposta_rh"', $query->toSql());
        $this->assertContains('aprovado', $query->getBindings());
        $this->assertStringNotContainsString('status_aprovacao_gestor_destino', $query->toSql());
    }

    public function test_filtro_reprovado_considera_todas_as_etapas(): void
    {
        $query = TransferenciaPrevista::query();
        (new TransferenciaPrevistaFilterApplier([
            'campoStatus' => 'reprovado',
            '_full_export_access' => true,
        ], $this->userStub()))->apply($query);

        $sql = $query->toSql();

        $this->assertStringContainsString('status_aprovacao_gestor_destino', $sql);
        $this->assertStringContainsString('status_aprovacao_gestor_unico', $sql);
        $this->assertStringContainsString('status_aprovacao_extra', $sql);
        $this->assertStringContainsString('resposta_rh', $sql);
        $this->assertContains('reprovado', $query->getBindings());
    }
}
