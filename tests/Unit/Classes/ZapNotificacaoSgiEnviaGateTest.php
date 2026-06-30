<?php

namespace Tests\Unit\Classes;

use App\Classes\ZapNotificacao;
use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use Mockery;
use Tests\TestCase;

class ZapNotificacaoSgiEnviaGateTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testSgiEnviaBloqueiaQuandoGateRejeita(): void
    {
        $gate = Mockery::mock(WhatsappNotificationGateService::class);
        $gate->shouldReceive('podeEnviar')
            ->once()
            ->with(TipoMensagemWhatsapp::CartaOfertaSgi, 10, null)
            ->andReturn(false);
        $this->app->instance(WhatsappNotificationGateService::class, $gate);

        $result = (new ZapNotificacao())->SgiEnvia([
            'telefone' => '5511999999999',
            'mensagem' => 'teste',
            '_whatsapp_meta' => ZapNotificacao::meta(TipoMensagemWhatsapp::CartaOfertaSgi, 10),
        ]);

        $this->assertFalse($result['status']);
    }
}
