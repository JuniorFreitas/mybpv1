<?php

namespace Tests\Unit\Domain\Whatsapp;

use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappBloqueioMetrics;
use App\Domain\Whatsapp\Services\WhatsappConfigService;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class WhatsappStatusNaoRegistraMetricasTest extends TestCase
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

    public function test_pode_enviar_sem_metricas_nao_incrementa_contadores(): void
    {
        $config = Mockery::mock(WhatsappConfigService::class);
        $config->shouldReceive('isModuloHabilitado')->andReturn(false);

        $gate = Mockery::mock(WhatsappNotificationGateService::class, [$config])->makePartial();
        $gate->shouldReceive('empresaPermiteWhatsapp')->andReturn(true);

        $before = app(WhatsappBloqueioMetrics::class)->totalDia();

        $this->assertFalse($gate->podeEnviar(
            TipoMensagemWhatsapp::RecrutamentoSelecao,
            200,
            null,
            false,
        ));

        $this->assertSame($before, app(WhatsappBloqueioMetrics::class)->totalDia());
    }

    public function test_pode_enviar_com_metricas_incrementa_motivo_modulo(): void
    {
        $config = Mockery::mock(WhatsappConfigService::class);
        $config->shouldReceive('isModuloHabilitado')->andReturn(false);

        $gate = Mockery::mock(WhatsappNotificationGateService::class, [$config])->makePartial();
        $gate->shouldReceive('empresaPermiteWhatsapp')->andReturn(true);

        $this->assertFalse($gate->podeEnviar(
            TipoMensagemWhatsapp::RecrutamentoSelecao,
            200,
            null,
            true,
        ));

        $this->assertSame(1, app(WhatsappBloqueioMetrics::class)->totalMotivo(
            WhatsappBloqueioMetrics::MOTIVO_MODULO
        ));
    }
}
