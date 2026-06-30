<?php

namespace Tests\Unit\Domain\Whatsapp;

use App\Domain\Whatsapp\Services\WhatsappConfigService;
use App\Domain\Whatsapp\Services\WhatsappMessageFactory;
use App\Domain\Whatsapp\Services\WhatsappTemplateRenderer;
use Mockery;
use Tests\TestCase;

class WhatsappMessageFactoryRodapeTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testSempreIncluiRodapeMybpQuandoTemplateNaoPossui(): void
    {
        $configService = Mockery::mock(WhatsappConfigService::class);
        $configService->shouldReceive('resolveContactData')->once()->andReturn([
            'nome_exibicao' => 'Empresa',
            'telefone_contato' => '',
            'endereco_completo' => '',
            'texto_assinatura' => null,
        ]);
        $configService->shouldReceive('getTemplateCorpo')->once()->andReturn('Olá {{nome_destinatario}}');

        $factory = new WhatsappMessageFactory($configService, new WhatsappTemplateRenderer());
        $mensagem = $factory->render(
            \App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp::ParecerRotaTransporte,
            1,
            ['nome_destinatario' => 'João']
        );

        $this->assertStringContainsString('MyBP', $mensagem);
        $this->assertStringContainsString('não responda', $mensagem);
    }

    public function testNaoDuplicaRodapeQuandoTemplateJaPossui(): void
    {
        $rodape = config('whatsapp_templates.rodape_padrao', '');
        $configService = Mockery::mock(WhatsappConfigService::class);
        $configService->shouldReceive('resolveContactData')->once()->andReturn([
            'nome_exibicao' => 'Empresa',
            'telefone_contato' => '',
            'endereco_completo' => '',
            'texto_assinatura' => null,
        ]);
        $configService->shouldReceive('getTemplateCorpo')->once()->andReturn("Olá\n\n{{rodape_mybp}}");

        $factory = new WhatsappMessageFactory($configService, new WhatsappTemplateRenderer());
        $mensagem = $factory->render(
            \App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp::RecrutamentoSelecao,
            1,
            []
        );

        $this->assertSame(1, substr_count($mensagem, 'MyBP'));
        $this->assertStringContainsString($rodape, $mensagem);
    }
}
