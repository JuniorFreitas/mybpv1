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
        $user->empresa_id = 1;

        return $user;
    }

    private function applyStatus(string $status): \Illuminate\Database\Eloquent\Builder
    {
        $query = TransferenciaPrevista::query();
        (new TransferenciaPrevistaFilterApplier([
            'campoStatus' => $status,
            '_full_export_access' => true,
        ], $this->userStub()))->apply($query);

        return $query;
    }

    public function test_filtro_aberto_exige_sem_rh_final_e_sem_reprovacao_em_qualquer_etapa(): void
    {
        $query = $this->applyStatus('aberto');
        $sql = $query->toSql();

        $this->assertStringContainsString('resposta_rh', $sql);
        $this->assertStringContainsString('status_aprovacao_gestor_destino', $sql);
        $this->assertStringContainsString('status_aprovacao_gestor_unico', $sql);
        $this->assertStringContainsString('status_aprovacao_extra', $sql);
        $this->assertContains('reprovado', $query->getBindings());
    }

    public function test_filtro_aprovado_usa_resposta_rh(): void
    {
        $query = $this->applyStatus('aprovado');

        $this->assertStringContainsString('"resposta_rh"', $query->toSql());
        $this->assertContains('aprovado', $query->getBindings());
        $this->assertStringNotContainsString('status_aprovacao_gestor_destino', $query->toSql());
    }

    public function test_filtro_reprovado_considera_todas_as_etapas(): void
    {
        $query = $this->applyStatus('reprovado');
        $sql = $query->toSql();

        $this->assertStringContainsString('status_aprovacao_gestor_destino', $sql);
        $this->assertStringContainsString('status_aprovacao_gestor_unico', $sql);
        $this->assertStringContainsString('status_aprovacao_extra', $sql);
        $this->assertStringContainsString('resposta_rh', $sql);
        $this->assertContains('reprovado', $query->getBindings());
    }

    public function test_filtro_pendente_gestor_origem_usa_modo_padrao_e_status_vazio(): void
    {
        $query = $this->applyStatus('pendente_gestor_origem');
        $sql = $query->toSql();

        $this->assertStringContainsString('status_aprovacao', $sql);
        $this->assertStringContainsString('modo_aprovacao', $sql);
        $this->assertContains(TransferenciaPrevista::MODO_APROVACAO_PADRAO, $query->getBindings());
        $this->assertStringContainsString('fluxo_gestores_automatico', $sql);
    }

    public function test_filtro_pendente_gestor_destino_exige_destino_e_origem_concluida(): void
    {
        $query = $this->applyStatus('pendente_gestor_destino');
        $sql = $query->toSql();

        $this->assertStringContainsString('exige_aprovacao_gestor_destino', $sql);
        $this->assertStringContainsString('status_aprovacao_gestor_destino', $sql);
        $this->assertContains('aprovado', $query->getBindings());
    }

    public function test_filtro_pendente_gestor_unico_usa_modo_gestor_unico(): void
    {
        $query = $this->applyStatus('pendente_gestor_unico');
        $sql = $query->toSql();

        $this->assertStringContainsString('modo_aprovacao', $sql);
        $this->assertStringContainsString('status_aprovacao_gestor_unico', $sql);
        $this->assertContains(TransferenciaPrevista::MODO_APROVACAO_GESTOR_UNICO, $query->getBindings());
        $this->assertStringContainsString('gestor_aprovacao_id', $sql);
    }

    public function test_filtro_pendente_rh_exige_gestores_concluidos(): void
    {
        $query = $this->applyStatus('pendente_rh');
        $sql = $query->toSql();

        $this->assertStringContainsString('resposta_rh', $sql);
        $this->assertStringContainsString('modo_aprovacao', $sql);
    }

    public function test_filtro_aprovado_gestor_origem(): void
    {
        $query = $this->applyStatus('aprovado_gestor_origem');

        $this->assertStringContainsString('status_aprovacao', $query->toSql());
        $this->assertContains('aprovado', $query->getBindings());
        $this->assertContains(TransferenciaPrevista::MODO_APROVACAO_PADRAO, $query->getBindings());
    }

    public function test_filtro_reprovado_gestor_destino(): void
    {
        $query = $this->applyStatus('reprovado_gestor_destino');

        $this->assertStringContainsString('status_aprovacao_gestor_destino', $query->toSql());
        $this->assertContains('reprovado', $query->getBindings());
    }

    public function test_filtro_aprovado_gestor_unico(): void
    {
        $query = $this->applyStatus('aprovado_gestor_unico');

        $this->assertContains(TransferenciaPrevista::MODO_APROVACAO_GESTOR_UNICO, $query->getBindings());
        $this->assertContains('aprovado', $query->getBindings());
    }

    public function test_filtro_reprovado_rh(): void
    {
        $query = $this->applyStatus('reprovado_rh');

        $this->assertStringContainsString('"resposta_rh"', $query->toSql());
        $this->assertContains('reprovado', $query->getBindings());
    }
}
