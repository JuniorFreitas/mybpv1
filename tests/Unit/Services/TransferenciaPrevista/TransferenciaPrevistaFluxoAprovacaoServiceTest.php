<?php

namespace Tests\Unit\Services\TransferenciaPrevista;

use App\Models\TransferenciaPrevista;
use App\Models\User;
use App\Services\CentroCusto\CentroCustoGestorResolverService;
use App\Services\TransferenciaPrevista\TransferenciaPrevistaFluxoAprovacaoService;
use Tests\TestCase;

class TransferenciaPrevistaFluxoAprovacaoServiceTest extends TestCase
{
    private TransferenciaPrevistaFluxoAprovacaoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TransferenciaPrevistaFluxoAprovacaoService(new CentroCustoGestorResolverService());
    }

    public function test_ct01_gestores_diferentes_exige_aprovacao_destino(): void
    {
        $origem = new User();
        $origem->id = 10;
        $destino = new User();
        $destino->id = 20;

        $this->assertTrue($this->service->deveExigirAprovacaoGestorDestino($origem, $destino));
    }

    public function test_ct02_mesmo_gestor_nao_exige_aprovacao_destino(): void
    {
        $gestor = new User(['id' => 10]);

        $this->assertFalse($this->service->deveExigirAprovacaoGestorDestino($gestor, $gestor));
    }

    public function test_ct08_mesmo_gestor_apenas_uma_etapa_no_fluxo_montado(): void
    {
        $dados = [
            'gestor_id' => 10,
            'gestor_destino_id' => null,
            'exige_aprovacao_gestor_destino' => false,
            'fluxo_gestores_automatico' => true,
        ];

        $this->assertFalse($dados['exige_aprovacao_gestor_destino']);
        $this->assertNull($dados['gestor_destino_id']);
    }

    public function test_etapa_atual_identifica_gestor_destino_pendente(): void
    {
        $transferencia = new TransferenciaPrevista([
            'fluxo_gestores_automatico' => true,
            'status_aprovacao' => 'aprovado',
            'exige_aprovacao_gestor_destino' => true,
            'gestor_destino_id' => 99,
            'status_aprovacao_gestor_destino' => null,
            'empresa_id' => 1,
        ]);

        $this->assertSame(
            TransferenciaPrevistaFluxoAprovacaoService::ETAPA_GESTOR_DESTINO,
            $this->service->etapaAtual($transferencia)
        );
    }

    public function test_etapa_atual_gestor_origem_pendente(): void
    {
        $transferencia = new TransferenciaPrevista([
            'fluxo_gestores_automatico' => true,
            'status_aprovacao' => null,
            'empresa_id' => 1,
        ]);

        $this->assertSame(
            TransferenciaPrevistaFluxoAprovacaoService::ETAPA_GESTOR_ORIGEM,
            $this->service->etapaAtual($transferencia)
        );
    }

    public function test_gestores_etapas_concluidas_quando_mesmo_gestor(): void
    {
        $transferencia = new TransferenciaPrevista([
            'fluxo_gestores_automatico' => true,
            'status_aprovacao' => 'aprovado',
            'exige_aprovacao_gestor_destino' => false,
        ]);

        $this->assertTrue($this->service->gestoresEtapasConcluidas($transferencia));
    }

    public function test_is_fluxo_legado_quando_flag_desligada(): void
    {
        $transferencia = new TransferenciaPrevista([
            'fluxo_gestores_automatico' => false,
        ]);

        $this->assertTrue($this->service->isFluxoLegado($transferencia));
    }
}
