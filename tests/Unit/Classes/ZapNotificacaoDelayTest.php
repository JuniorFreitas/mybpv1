<?php

namespace Tests\Unit\Classes;

use App\Classes\ZapNotificacao;
use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use App\Jobs\JobSendNotificacaoWhatsApp;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class ZapNotificacaoDelayTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testEnviarDisparaJobComDelayMinimo(): void
    {
        Queue::fake();

        putenv('AMBIENTE=local');
        $_ENV['AMBIENTE'] = 'local';
        $_SERVER['AMBIENTE'] = 'local';

        $gate = Mockery::mock(WhatsappNotificationGateService::class);
        $gate->shouldReceive('podeEnviar')->once()->andReturn(true);
        $this->app->instance(WhatsappNotificationGateService::class, $gate);

        (new ZapNotificacao())->enviar([
            'enviado_id' => 1,
            'telefone' => '5511999999999',
            'mensagem' => 'teste delay',
            '_whatsapp_meta' => ZapNotificacao::meta(
                TipoMensagemWhatsapp::RecrutamentoSelecao,
                1,
            ),
        ], 12);

        Queue::assertPushed(JobSendNotificacaoWhatsApp::class, function (JobSendNotificacaoWhatsApp $job) {
            return $job->delay !== null
                && $job->delay->greaterThanOrEqualTo(now()->addSeconds(11));
        });
    }

    public function testCalcularDelayFilaIncrementaPorIndice(): void
    {
        $minComIndice1 = ZapNotificacao::DELAY_MIN_SEGUNDOS + ZapNotificacao::DELAY_INTERVALO_LOTE_SEGUNDOS;
        $maxComIndice1 = ZapNotificacao::DELAY_MAX_SEGUNDOS + ZapNotificacao::DELAY_INTERVALO_LOTE_SEGUNDOS;

        $delay = ZapNotificacao::calcularDelayFila(1);

        $this->assertGreaterThanOrEqual($minComIndice1, $delay);
        $this->assertLessThanOrEqual($maxComIndice1, $delay);
    }
}
