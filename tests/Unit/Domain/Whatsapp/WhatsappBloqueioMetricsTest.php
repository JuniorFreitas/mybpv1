<?php

namespace Tests\Unit\Domain\Whatsapp;

use App\Classes\ZapNotificacao;
use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappBloqueioMetrics;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class WhatsappBloqueioMetricsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_registrar_incrementa_total_empresa_e_motivo(): void
    {
        $metrics = new WhatsappBloqueioMetrics();

        $metrics->registrar(WhatsappBloqueioMetrics::MOTIVO_MODULO, 104, [
            'tipo' => 'recrutamento_selecao',
            'modulo' => 'Recrutamento',
        ]);
        $metrics->registrar(WhatsappBloqueioMetrics::MOTIVO_MODULO, 104);

        $this->assertSame(2, $metrics->totalDia());
        $this->assertSame(2, $metrics->totalEmpresa(104));
        $this->assertSame(2, $metrics->totalMotivo(WhatsappBloqueioMetrics::MOTIVO_MODULO));
    }

    public function test_zap_sem_meta_registra_motivo_meta_ausente(): void
    {
        Queue::fake();

        (new ZapNotificacao())->enviar([
            'enviado_id' => 1,
            'telefone' => '5511999999999',
            'mensagem' => 'teste',
        ]);

        Queue::assertNothingPushed();
        $this->assertSame(1, app(WhatsappBloqueioMetrics::class)->totalMotivo(
            WhatsappBloqueioMetrics::MOTIVO_META_AUSENTE
        ));
    }

    public function test_gate_modulo_desabilitado_registra_motivo_modulo(): void
    {
        Queue::fake();

        $gate = Mockery::mock(WhatsappNotificationGateService::class)->makePartial();
        $gate->shouldReceive('empresaPermiteWhatsapp')->andReturn(true);
        $gate->shouldReceive('podeEnviar')->andReturnUsing(function () {
            app(WhatsappBloqueioMetrics::class)->registrar(
                WhatsappBloqueioMetrics::MOTIVO_MODULO,
                104,
                ['tipo' => TipoMensagemWhatsapp::RecrutamentoSelecao->value, 'modulo' => 'Recrutamento'],
            );

            return false;
        });
        $this->app->instance(WhatsappNotificationGateService::class, $gate);

        (new ZapNotificacao())->enviar([
            'enviado_id' => 1,
            'telefone' => '5511999999999',
            'mensagem' => 'teste',
            '_whatsapp_meta' => ZapNotificacao::meta(TipoMensagemWhatsapp::RecrutamentoSelecao, 104),
        ]);

        Queue::assertNothingPushed();
        $this->assertGreaterThanOrEqual(1, app(WhatsappBloqueioMetrics::class)->totalMotivo(
            WhatsappBloqueioMetrics::MOTIVO_MODULO
        ));
    }
}
