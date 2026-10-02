<?php

namespace Tests\Unit\Services\Concerns;

use App\Models\AdmissoesPrevista;
use App\Models\User;
use App\Services\AdmissoesPrevista\AdmissoesPrevistaFilterApplier;
use Tests\TestCase;

class AppliesApprovalFlowStatusFilterTest extends TestCase
{
    private function userStub(): User
    {
        $user = new User();
        $user->id = 1;
        $user->empresa_id = 1;

        return $user;
    }

    private function apply(string $status): \Illuminate\Database\Eloquent\Builder
    {
        $query = AdmissoesPrevista::query();
        (new AdmissoesPrevistaFilterApplier([
            'campoStatusAprovacao' => $status,
            '_full_export_access' => true,
        ], $this->userStub()))->apply($query);

        return $query;
    }

    public function test_pendente_gestor_exige_status_gestor_vazio_e_em_andamento(): void
    {
        $query = $this->apply('pendente_gestor');
        $sql = $query->toSql();

        $this->assertStringContainsString('status_aprovacao', $sql);
        $this->assertStringContainsString('status_aprovacao_rh', $sql);
        $this->assertContains(AdmissoesPrevista::STATUS_REPROVADO, $query->getBindings());
    }

    public function test_aprovado_gestor_filtra_coluna_gestor(): void
    {
        $query = $this->apply('aprovado_gestor');

        $this->assertStringContainsString('status_aprovacao', $query->toSql());
        $this->assertContains(AdmissoesPrevista::STATUS_APROVADO, $query->getBindings());
    }

    public function test_reprovado_gestor_filtra_coluna_gestor(): void
    {
        $query = $this->apply('reprovado_gestor');

        $this->assertContains(AdmissoesPrevista::STATUS_REPROVADO, $query->getBindings());
    }

    public function test_reprovado_rh_filtra_resposta_rh(): void
    {
        $query = $this->apply('reprovado_rh');

        $this->assertStringContainsString('status_aprovacao_rh', $query->toSql());
        $this->assertContains(AdmissoesPrevista::STATUS_REPROVADO, $query->getBindings());
    }

    public function test_pendente_rh_exige_gestor_aprovado_quando_sem_extra(): void
    {
        $query = $this->apply('pendente_rh');
        $sql = $query->toSql();

        $this->assertStringContainsString('status_aprovacao', $sql);
        $this->assertContains(AdmissoesPrevista::STATUS_APROVADO, $query->getBindings());
    }

    public function test_aberto_e_pendente_sem_rh_final_e_sem_reprovacao(): void
    {
        $query = $this->apply('aberto');
        $sql = $query->toSql();

        $this->assertStringContainsString('status_aprovacao_rh', $sql);
        $this->assertContains(AdmissoesPrevista::STATUS_REPROVADO, $query->getBindings());
        $this->assertNotContains(AdmissoesPrevista::STATUS_APROVADO, $query->getBindings());
    }

    public function test_reprovado_qualquer_etapa(): void
    {
        $query = $this->apply('reprovado');
        $sql = $query->toSql();

        $this->assertStringContainsString('status_aprovacao_extra', $sql);
        $this->assertStringContainsString('status_aprovacao_rh', $sql);
        $this->assertContains(AdmissoesPrevista::STATUS_REPROVADO, $query->getBindings());
    }
}
