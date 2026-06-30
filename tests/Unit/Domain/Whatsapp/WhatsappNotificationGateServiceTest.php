<?php

namespace Tests\Unit\Domain\Whatsapp;

use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappConfigService;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use Mockery;
use Tests\TestCase;

class WhatsappNotificationGateServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @return array<string, array{0: TipoMensagemWhatsapp, 1: string}> */
    public static function tiposWhatsappProvider(): array
    {
        $tiposConfig = (require dirname(__DIR__, 4) . '/config/whatsapp_templates.php')['tipos'];
        $casos = [];

        foreach (TipoMensagemWhatsapp::cases() as $tipo) {
            $casos[$tipo->value] = [$tipo, $tiposConfig[$tipo->value]['modulo']];
        }

        return $casos;
    }

    /**
     * @dataProvider tiposWhatsappProvider
     */
    public function testBloqueiaQualquerTipoQuandoEmpresaSemWhatsappLiberado(
        TipoMensagemWhatsapp $tipo,
    ): void {
        $configService = Mockery::mock(WhatsappConfigService::class);

        $gate = Mockery::mock(WhatsappNotificationGateService::class, [$configService])
            ->makePartial();
        $gate->shouldReceive('empresaPermiteWhatsapp')->once()->with(104)->andReturn(false);
        $configService->shouldNotReceive('isModuloHabilitado');

        $this->assertFalse($gate->podeEnviar($tipo, 104));
    }

    /**
     * @dataProvider tiposWhatsappProvider
     */
    public function testBloqueiaQualquerTipoQuandoModuloDesabilitadoNaEmpresa(
        TipoMensagemWhatsapp $tipo,
        string $modulo,
    ): void {
        $configService = Mockery::mock(WhatsappConfigService::class);

        $gate = Mockery::mock(WhatsappNotificationGateService::class, [$configService])
            ->makePartial();
        $gate->shouldReceive('empresaPermiteWhatsapp')->once()->with(104)->andReturn(true);
        $configService->shouldReceive('isModuloHabilitado')
            ->once()
            ->with(104, $modulo)
            ->andReturn(false);

        $this->assertFalse($gate->podeEnviar($tipo, 104, 10));
    }

    /**
     * @dataProvider tiposWhatsappProvider
     */
    public function testPermiteQualquerTipoQuandoModuloHabilitado(
        TipoMensagemWhatsapp $tipo,
        string $modulo,
    ): void {
        $configService = Mockery::mock(WhatsappConfigService::class);
        $configService->shouldReceive('isModuloHabilitado')
            ->once()
            ->with(104, $modulo)
            ->andReturn(true);

        $gate = Mockery::mock(WhatsappNotificationGateService::class, [$configService])
            ->makePartial();
        $gate->shouldReceive('empresaPermiteWhatsapp')->once()->with(104)->andReturn(true);

        $this->assertTrue($gate->podeEnviar($tipo, 104));
    }
}
