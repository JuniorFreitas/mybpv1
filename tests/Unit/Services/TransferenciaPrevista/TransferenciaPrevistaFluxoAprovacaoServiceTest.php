<?php

namespace Tests\Unit\Services\TransferenciaPrevista;

use App\Models\CentroCusto;
use App\Models\TransferenciaPrevista;
use App\Models\User;
use App\Services\CentroCusto\CentroCustoGestorResolverService;
use App\Services\TransferenciaPrevista\TransferenciaPrevistaFluxoAprovacaoService;
use DomainException;
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
            'gestor_id' => 10,
            'status_aprovacao' => null,
            'empresa_id' => 1,
        ]);

        $this->assertSame(
            TransferenciaPrevistaFluxoAprovacaoService::ETAPA_GESTOR_ORIGEM,
            $this->service->etapaAtual($transferencia)
        );
    }

    public function test_sem_gestor_origem_nao_exige_etapa_origem(): void
    {
        $origem = new User();
        $origem->id = 10;
        $destino = new User();
        $destino->id = 20;

        $this->assertTrue($this->service->deveExigirAprovacaoGestorDestino(null, $destino));
        $this->assertTrue($this->service->deveExigirAprovacaoGestorDestino($origem, $destino));
    }

    public function test_etapa_atual_pula_origem_quando_nao_ha_gestor(): void
    {
        $transferencia = new TransferenciaPrevista([
            'fluxo_gestores_automatico' => true,
            'gestor_id' => null,
            'status_aprovacao' => null,
            'exige_aprovacao_gestor_destino' => true,
            'gestor_destino_id' => 20,
            'status_aprovacao_gestor_destino' => null,
            'empresa_id' => 1,
        ]);

        $this->assertSame(
            TransferenciaPrevistaFluxoAprovacaoService::ETAPA_GESTOR_DESTINO,
            $this->service->etapaAtual($transferencia)
        );
        $this->assertTrue($this->service->origemEtapaConcluida($transferencia));
        $this->assertFalse($this->service->exigeAprovacaoGestorOrigem($transferencia));
    }

    public function test_pode_aprovar_gestor_destino_quando_origem_dispensada(): void
    {
        $aprovador = new User();
        $aprovador->id = 20;

        $transferencia = new TransferenciaPrevista([
            'fluxo_gestores_automatico' => true,
            'gestor_id' => null,
            'status_aprovacao' => null,
            'exige_aprovacao_gestor_destino' => true,
            'gestor_destino_id' => 20,
            'status_aprovacao_gestor_destino' => null,
            'empresa_id' => 1,
        ]);

        $this->assertTrue($this->service->podeAprovarGestorDestino($transferencia, $aprovador));
        $this->assertFalse($this->service->podeAprovarGestorOrigem($transferencia, $aprovador));
    }

    public function test_rh_pode_aprovar_gestor_origem_e_destino(): void
    {
        $rh = $this->usuarioComPrivilegioRh(5);

        $origemPendente = new TransferenciaPrevista([
            'fluxo_gestores_automatico' => true,
            'gestor_id' => 10,
            'status_aprovacao' => null,
            'empresa_id' => 1,
        ]);

        $destinoPendente = new TransferenciaPrevista([
            'fluxo_gestores_automatico' => true,
            'gestor_id' => 10,
            'status_aprovacao' => 'aprovado',
            'exige_aprovacao_gestor_destino' => true,
            'gestor_destino_id' => 20,
            'status_aprovacao_gestor_destino' => null,
            'empresa_id' => 1,
        ]);

        $this->assertTrue($this->service->podeAprovarGestorOrigem($origemPendente, $rh));
        $this->assertTrue($this->service->podeAprovarGestorDestino($destinoPendente, $rh));
        $this->assertFalse($this->service->podeAprovarGestorDestino($origemPendente, $rh));
    }

    public function test_sem_privilegio_rh_nao_aprova_etapa_de_outro_gestor(): void
    {
        $outro = new User();
        $outro->id = 99;

        $origemPendente = new TransferenciaPrevista([
            'fluxo_gestores_automatico' => true,
            'gestor_id' => 10,
            'status_aprovacao' => null,
            'empresa_id' => 1,
        ]);

        $this->assertFalse($this->service->podeAprovarGestorOrigem($origemPendente, $outro));
    }

    public function test_gestores_etapas_concluidas_quando_origem_dispensada_e_destino_aprovado(): void
    {
        $transferencia = new TransferenciaPrevista([
            'fluxo_gestores_automatico' => true,
            'gestor_id' => null,
            'status_aprovacao' => null,
            'exige_aprovacao_gestor_destino' => true,
            'status_aprovacao_gestor_destino' => 'aprovado',
        ]);

        $this->assertTrue($this->service->gestoresEtapasConcluidas($transferencia));
    }

    public function test_gestores_etapas_nao_concluidas_quando_origem_dispensada_e_destino_pendente(): void
    {
        $transferencia = new TransferenciaPrevista([
            'fluxo_gestores_automatico' => true,
            'gestor_id' => null,
            'status_aprovacao' => null,
            'exige_aprovacao_gestor_destino' => true,
            'status_aprovacao_gestor_destino' => null,
        ]);

        $this->assertFalse($this->service->gestoresEtapasConcluidas($transferencia));
    }

    public function test_resolver_gestor_origem_retorna_null_quando_centro_nao_tem_gestor(): void
    {
        $resolver = $this->createMock(CentroCustoGestorResolverService::class);
        $resolver->method('getGestorPrincipal')->willReturn(null);

        $service = new TransferenciaPrevistaFluxoAprovacaoService($resolver);

        $this->assertNull($service->resolverGestorOrigem(15, 99));
    }

    public function test_resolver_gestor_destino_lanca_excecao_sem_gestor(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(TransferenciaPrevistaFluxoAprovacaoService::MSG_GESTOR_DESTINO_AUSENTE);

        $resolver = $this->createMock(CentroCustoGestorResolverService::class);
        $resolver->method('getGestorPrincipal')->willReturn(null);

        $service = new TransferenciaPrevistaFluxoAprovacaoService($resolver);
        $service->resolverGestorDestino(15, 99);
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

    public function test_origem_permite_centro_custo_inativo_destino_nao(): void
    {
        $this->assertFalse($this->service->exigirCentroCustoAtivo('origem'));
        $this->assertTrue($this->service->exigirCentroCustoAtivo('destino'));
    }

    public function test_is_fluxo_legado_quando_flag_desligada(): void
    {
        $transferencia = new TransferenciaPrevista([
            'fluxo_gestores_automatico' => false,
        ]);

        $this->assertTrue($this->service->isFluxoLegado($transferencia));
    }

    private function usuarioComPrivilegioRh(int $id): User
    {
        $user = new class extends User {
            public function can($ability, $arguments = []): bool
            {
                return $ability === 'privilegio_gestao_rh';
            }
        };
        $user->id = $id;

        return $user;
    }

    public function test_anexa_quem_aprovou_quando_rh_nao_e_gestor_do_cc(): void
    {
        $rh = $this->usuarioComPrivilegioRh(5);
        $rh->nome = 'Maria RH';

        $transferencia = new TransferenciaPrevista([
            'fluxo_gestores_automatico' => true,
            'gestor_id' => 10,
            'gestor_destino_id' => 20,
        ]);

        $this->assertTrue($this->service->aprovacaoGestorViaRh(
            $transferencia,
            $rh,
            TransferenciaPrevistaFluxoAprovacaoService::ETAPA_GESTOR_ORIGEM
        ));
        $this->assertTrue($this->service->aprovacaoGestorViaRh(
            $transferencia,
            $rh,
            TransferenciaPrevistaFluxoAprovacaoService::ETAPA_GESTOR_DESTINO
        ));
        $this->assertSame(
            "Obs\nRegistrado por RH: Maria RH",
            $this->service->anexarRegistroAprovadorRh('Obs', $rh)
        );
    }

    public function test_gestor_do_cc_com_privilegio_rh_nao_registra_como_via_rh(): void
    {
        $gestor = $this->usuarioComPrivilegioRh(10);
        $gestor->nome = 'Carlos Gestor';

        $transferencia = new TransferenciaPrevista([
            'gestor_id' => 10,
            'gestor_destino_id' => 10,
        ]);

        $this->assertFalse($this->service->aprovacaoGestorViaRh(
            $transferencia,
            $gestor,
            TransferenciaPrevistaFluxoAprovacaoService::ETAPA_GESTOR_ORIGEM
        ));
        $this->assertFalse($this->service->aprovacaoGestorViaRh(
            $transferencia,
            $gestor,
            TransferenciaPrevistaFluxoAprovacaoService::ETAPA_GESTOR_DESTINO
        ));
    }

    public function test_decisao_rh_usa_resposta_rh_enviada_pela_tela(): void
    {
        $this->assertSame('aprovado', $this->service->decisaoAprovacaoRh(['resposta_rh' => 'aprovado']));
        $this->assertSame('reprovado', $this->service->decisaoAprovacaoRh(['resposta_rh' => 'reprovado']));
    }

    public function test_decisao_rh_aceita_status_aprovacao_rh_legado(): void
    {
        $this->assertSame('aprovado', $this->service->decisaoAprovacaoRh(['status_aprovacao_rh' => 'aprovado']));
    }

    public function test_decisao_rh_prioriza_resposta_rh_quando_os_dois_existem(): void
    {
        $this->assertSame('aprovado', $this->service->decisaoAprovacaoRh([
            'resposta_rh' => 'aprovado',
            'status_aprovacao_rh' => 'reprovado',
        ]));
    }

    public function test_decisao_rh_vazia_nao_efetiva(): void
    {
        $this->assertNull($this->service->decisaoAprovacaoRh([]));
        $this->assertNull($this->service->decisaoAprovacaoRh(['resposta_rh' => '']));
        $this->assertNull($this->service->decisaoAprovacaoRh(['status_aprovacao_rh' => '']));
    }

    public function test_historico_rh_informa_centro_custo_origem_e_destino(): void
    {
        $transferencia = new TransferenciaPrevista();
        $transferencia->id = 77;
        $transferencia->setRelation('CentroCustoOrigem', new CentroCusto(['label' => 'CC Origem']));
        $transferencia->setRelation('CentroCustoDestino', new CentroCusto(['label' => 'CC Destino']));

        $this->assertSame(
            'Solicitação foi aprovado pelo RH na mudança de Centro de Custo de CC Origem para CC Destino na solicitação de transferência #77',
            $this->service->mensagemHistoricoAprovacaoRh($transferencia, 'aprovado')
        );
    }

    public function test_historico_rh_usa_fallback_quando_cc_nao_informado(): void
    {
        $transferencia = new TransferenciaPrevista();
        $transferencia->id = 8;
        $transferencia->setRelation('CentroCustoOrigem', null);
        $transferencia->setRelation('CentroCustoDestino', new CentroCusto(['label' => 'CC Destino']));

        $this->assertSame(
            'Solicitação foi reprovado pelo RH na mudança de Centro de Custo de não informado para CC Destino na solicitação de transferência #8',
            $this->service->mensagemHistoricoAprovacaoRh($transferencia, 'reprovado')
        );
    }
}
