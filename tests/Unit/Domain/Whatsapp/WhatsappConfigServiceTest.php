<?php

namespace Tests\Unit\Domain\Whatsapp;

use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappConfigService;
use App\Models\EmpresaWhatsappConfig;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WhatsappConfigServiceTest extends TestCase
{
    private WhatsappConfigService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WhatsappConfigService();
    }

    public function testInvalidateCacheRemoveChave(): void
    {
        Cache::put('whatsapp_config:50', ['config' => 'old', 'templates' => []], 900);

        $this->service->invalidateCache(50);

        $this->assertFalse(Cache::has('whatsapp_config:50'));
    }

    public function testGetTemplateCorpoRetornaPadraoQuandoSemCustomizacao(): void
    {
        Cache::put('whatsapp_config:1', ['config' => null, 'templates' => []], 900);

        $corpo = $this->service->getTemplateCorpo(1, TipoMensagemWhatsapp::ParecerRotaTransporte);

        $this->assertStringContainsString('{{nome_destinatario}}', $corpo);
        $this->assertStringContainsString('{{rota}}', $corpo);
    }

    public function testCacheTtlEmMinutos(): void
    {
        $this->assertSame(900, $this->service->cacheTtlSeconds());
    }

    public function testModulosPadraoHabilitadosQuandoSemConfig(): void
    {
        Cache::put('whatsapp_config:77', ['config' => null, 'templates' => []], 900);

        $modulos = $this->service->getModulosHabilitados(77);

        $this->assertNotEmpty($modulos);
        $this->assertTrue($modulos['Movimentação'] ?? false);
        $this->assertTrue($modulos['Recrutamento'] ?? false);
    }

    public function testModuloDesabilitadoRetornaFalse(): void
    {
        $config = new EmpresaWhatsappConfig([
            'modulos_habilitados' => [
                'Recrutamento' => false,
                'Movimentação' => true,
            ],
        ]);

        Cache::put('whatsapp_config:88', [
            'config' => $config,
            'templates' => [],
        ], 900);

        $this->assertFalse($this->service->isModuloHabilitado(88, 'Recrutamento'));
        $this->assertTrue($this->service->isModuloHabilitado(88, 'Movimentação'));
        $this->assertFalse($this->service->isModuloHabilitado(88, 'ModuloInexistente'));
    }

    /** @return array<string, array{0: string}> */
    public static function modulosWhatsappProvider(): array
    {
        $tiposConfig = (require dirname(__DIR__, 4) . '/config/whatsapp_templates.php')['tipos'];
        $casos = [];

        foreach (array_unique(array_column($tiposConfig, 'modulo')) as $modulo) {
            $casos[$modulo] = [$modulo];
        }

        return $casos;
    }

    #[DataProvider('modulosWhatsappProvider')]
    public function testCadaModuloPodeSerDesabilitadoIndividualmente(string $modulo): void
    {
        $todosModulos = array_keys(self::modulosWhatsappProvider());
        $modulos = array_fill_keys($todosModulos, true);
        $modulos[$modulo] = false;

        $config = new EmpresaWhatsappConfig([
            'modulos_habilitados' => $modulos,
        ]);

        Cache::put('whatsapp_config:91', [
            'config' => $config,
            'templates' => [],
        ], 900);

        $this->assertFalse($this->service->isModuloHabilitado(91, $modulo));

        foreach ($todosModulos as $outro) {
            if ($outro === $modulo) {
                continue;
            }

            $this->assertTrue(
                $this->service->isModuloHabilitado(91, $outro),
                "Módulo {$outro} deveria permanecer habilitado",
            );
        }
    }

    public function testEnsureConfigMaterializadaCriaJsonPadrao(): void
    {
        config(['activitylog.enabled' => false]);
        Schema::dropIfExists('empresa_whatsapp_configs');
        Schema::create('empresa_whatsapp_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->unique();
            $table->string('nome_exibicao')->nullable();
            $table->string('telefone_contato')->nullable();
            $table->text('endereco_completo')->nullable();
            $table->text('texto_assinatura')->nullable();
            $table->json('modulos_habilitados')->nullable();
            $table->timestamps();
        });

        $config = $this->service->ensureConfigMaterializada(55);

        $this->assertTrue($config->exists);
        $this->assertSame(55, (int) $config->empresa_id);
        $this->assertIsArray($config->modulos_habilitados);
        $this->assertNotEmpty($config->modulos_habilitados);
        $this->assertTrue($config->modulos_habilitados['Recrutamento'] ?? false);

        $mesmo = $this->service->ensureConfigMaterializada(55);
        $this->assertSame($config->id, $mesmo->id);
    }
}
