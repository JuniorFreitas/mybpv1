<?php

namespace Tests\Unit\Jobs\Movimentacao\TransferenciaPrevista;

use App\Jobs\Movimentacao\TransferenciaPrevista\JobNotificacaoRecursiva;
use App\Models\ClienteConfig;
use App\Models\TransferenciaPrevista;
use App\Models\User;
use App\Services\TransferenciaPrevista\TransferenciaPrevistaFluxoAprovacaoService;
use ReflectionMethod;
use Tests\TestCase;

class JobNotificacaoRecursivaGestorOrigemTest extends TestCase
{
    public function test_get_config_default_true_quando_chave_ausente(): void
    {
        $config = new ClienteConfig(['configuracoes' => []]);

        $this->assertTrue(
            filter_var($config->getConfig('transferencia_notificar_gestor_origem', true), FILTER_VALIDATE_BOOLEAN)
        );
    }

    public function test_get_config_false_quando_desabilitado(): void
    {
        $config = new ClienteConfig([
            'configuracoes' => ['transferencia_notificar_gestor_origem' => false],
        ]);

        $this->assertFalse(
            filter_var($config->getConfig('transferencia_notificar_gestor_origem', true), FILTER_VALIDATE_BOOLEAN)
        );
    }

    public function test_buscar_destinatarios_origem_vazio_quando_notificacao_desabilitada(): void
    {
        $job = new JobNotificacaoRecursiva(1, 1);

        $gestor = new User();
        $gestor->login = 'gestor.origem@example.com';

        $transferencia = new TransferenciaPrevista();
        $transferencia->setRelation('GestorOrigem', $gestor);

        $refTransferencia = new \ReflectionProperty(JobNotificacaoRecursiva::class, 'transferencia');
        $refTransferencia->setAccessible(true);
        $refTransferencia->setValue($job, $transferencia);

        $refCache = new \ReflectionProperty(JobNotificacaoRecursiva::class, 'cacheNotificarGestorOrigem');
        $refCache->setAccessible(true);
        $refCache->setValue($job, false);

        $method = new ReflectionMethod(JobNotificacaoRecursiva::class, 'buscarDestinatarios');
        $method->setAccessible(true);

        $this->assertSame([], $method->invoke($job, 'criacao_gestor_origem'));
        $this->assertSame([], $method->invoke($job, 'criacao'));
    }

    public function test_buscar_destinatarios_origem_quando_notificacao_habilitada(): void
    {
        $job = new JobNotificacaoRecursiva(1, 1);

        $gestor = new User();
        $gestor->login = 'gestor.origem@example.com';

        $transferencia = new TransferenciaPrevista();
        $transferencia->setRelation('GestorOrigem', $gestor);

        $refTransferencia = new \ReflectionProperty(JobNotificacaoRecursiva::class, 'transferencia');
        $refTransferencia->setAccessible(true);
        $refTransferencia->setValue($job, $transferencia);

        $refCache = new \ReflectionProperty(JobNotificacaoRecursiva::class, 'cacheNotificarGestorOrigem');
        $refCache->setAccessible(true);
        $refCache->setValue($job, true);

        $method = new ReflectionMethod(JobNotificacaoRecursiva::class, 'buscarDestinatarios');
        $method->setAccessible(true);

        $this->assertSame(['gestor.origem@example.com'], $method->invoke($job, 'criacao_gestor_origem'));
    }

    public function test_determinar_tipo_retorna_null_quando_origem_pendente_e_notificacao_off(): void
    {
        $job = new JobNotificacaoRecursiva(1, 1);

        $transferencia = new TransferenciaPrevista([
            'fluxo_gestores_automatico' => true,
            'gestor_id' => 10,
            'status_aprovacao' => null,
            'empresa_id' => 1,
        ]);

        $refTransferencia = new \ReflectionProperty(JobNotificacaoRecursiva::class, 'transferencia');
        $refTransferencia->setAccessible(true);
        $refTransferencia->setValue($job, $transferencia);

        $refCache = new \ReflectionProperty(JobNotificacaoRecursiva::class, 'cacheNotificarGestorOrigem');
        $refCache->setAccessible(true);
        $refCache->setValue($job, false);

        $method = new ReflectionMethod(JobNotificacaoRecursiva::class, 'determinarTipoNotificacao');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($job));
    }

    public function test_determinar_tipo_criacao_gestor_origem_quando_notificacao_on(): void
    {
        $job = new JobNotificacaoRecursiva(1, 1);

        $transferencia = new TransferenciaPrevista([
            'fluxo_gestores_automatico' => true,
            'gestor_id' => 10,
            'status_aprovacao' => null,
            'empresa_id' => 1,
        ]);

        $refTransferencia = new \ReflectionProperty(JobNotificacaoRecursiva::class, 'transferencia');
        $refTransferencia->setAccessible(true);
        $refTransferencia->setValue($job, $transferencia);

        $refCache = new \ReflectionProperty(JobNotificacaoRecursiva::class, 'cacheNotificarGestorOrigem');
        $refCache->setAccessible(true);
        $refCache->setValue($job, true);

        $method = new ReflectionMethod(JobNotificacaoRecursiva::class, 'determinarTipoNotificacao');
        $method->setAccessible(true);

        $this->assertSame('criacao_gestor_origem', $method->invoke($job));
    }

    public function test_get_config_exigir_aprovacao_origem_default_true(): void
    {
        $config = new ClienteConfig(['configuracoes' => []]);

        $this->assertTrue(
            filter_var($config->getConfig('transferencia_exigir_aprovacao_gestor_origem', true), FILTER_VALIDATE_BOOLEAN)
        );
    }

    public function test_exige_gestor_origem_na_notificacao_respeita_service(): void
    {
        $fluxo = \Mockery::mock(TransferenciaPrevistaFluxoAprovacaoService::class);
        $fluxo->shouldReceive('empresaExigeAprovacaoGestorOrigem')
            ->once()
            ->with(55)
            ->andReturn(false);
        $this->app->instance(TransferenciaPrevistaFluxoAprovacaoService::class, $fluxo);

        $job = new JobNotificacaoRecursiva(1, 55);
        $transferencia = new TransferenciaPrevista([
            'modo_aprovacao' => 'padrao',
            'empresa_id' => 55,
        ]);

        $refTransferencia = new \ReflectionProperty(JobNotificacaoRecursiva::class, 'transferencia');
        $refTransferencia->setAccessible(true);
        $refTransferencia->setValue($job, $transferencia);

        $method = new ReflectionMethod(JobNotificacaoRecursiva::class, 'exigeGestorOrigemNaNotificacao');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($job));
    }

    public function test_origem_ja_aprovada_dispara_tipo_destino(): void
    {
        $job = new JobNotificacaoRecursiva(1, 1);

        $transferencia = new TransferenciaPrevista([
            'fluxo_gestores_automatico' => true,
            'gestor_id' => 10,
            'status_aprovacao' => 'aprovado',
            'exige_aprovacao_gestor_destino' => true,
            'gestor_destino_id' => 20,
            'status_aprovacao_gestor_destino' => null,
            'empresa_id' => 1,
        ]);

        $refTransferencia = new \ReflectionProperty(JobNotificacaoRecursiva::class, 'transferencia');
        $refTransferencia->setAccessible(true);
        $refTransferencia->setValue($job, $transferencia);

        $method = new ReflectionMethod(JobNotificacaoRecursiva::class, 'determinarTipoNotificacao');
        $method->setAccessible(true);

        $this->assertSame('criacao_gestor_destino', $method->invoke($job));
    }
}
