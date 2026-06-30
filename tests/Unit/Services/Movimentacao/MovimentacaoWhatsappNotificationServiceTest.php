<?php

namespace Tests\Unit\Services\Movimentacao;

use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use App\Domain\Whatsapp\Services\WhatsappUsuarioTelefoneResolver;
use App\Services\Movimentacao\MovimentacaoWhatsappNotificationService;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class MovimentacaoWhatsappNotificationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testNaoEnviaQuandoModuloMovimentacaoDesabilitadoNaEmpresa(): void
    {
        Queue::fake();

        $gate = Mockery::mock(WhatsappNotificationGateService::class);
        $gate->shouldReceive('podeEnviar')
            ->once()
            ->with(TipoMensagemWhatsapp::MovimentacaoAprovacao, 104)
            ->andReturn(false);

        $telefoneResolver = Mockery::mock(WhatsappUsuarioTelefoneResolver::class);
        $telefoneResolver->shouldNotReceive('resolverNumeroEnvio');

        $service = new MovimentacaoWhatsappNotificationService($gate, $telefoneResolver);

        $service->enviarNotificacaoAprovacao(
            104,
            ['gestor@empresa.com'],
            'Férias',
            'criacao',
            'Colaborador Teste',
            'https://mybp.test/movimentacao',
        );

        Queue::assertNothingPushed();
    }
}
