<?php

namespace Tests\Unit\Domain\Whatsapp;

use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappConfigService;
use App\Domain\Whatsapp\Services\WhatsappMessageFactory;
use App\Domain\Whatsapp\Services\WhatsappTemplateRenderer;
use Mockery;
use Tests\TestCase;

class WhatsappMessageFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testRenderUsaTemplatePadraoQuandoEmpresaNaoCustomizou(): void
    {
        $configService = Mockery::mock(WhatsappConfigService::class);
        $configService->shouldReceive('resolveContactData')->once()->with(999)->andReturn([
            'nome_exibicao' => 'Empresa Teste',
            'telefone_contato' => '98999999999',
            'endereco_completo' => 'Endereço',
            'texto_assinatura' => null,
        ]);
        $configService->shouldReceive('getTemplateCorpo')->once()->andReturn(
            'Olá {{nome_destinatario}}, rota {{rota}} — {{empresa_nome}}'
        );

        $factory = new WhatsappMessageFactory($configService, new WhatsappTemplateRenderer());

        $mensagem = $factory->render(
            TipoMensagemWhatsapp::ParecerRotaTransporte,
            999,
            [
                'nome_destinatario' => 'Maria',
                'rota' => 'Linha 10',
            ]
        );

        $this->assertStringStartsWith('Olá Maria, rota Linha 10 — Empresa Teste', $mensagem);
        $this->assertStringContainsString('MyBP', $mensagem);
    }

    public function testRenderUsaTemplateRetornadoPeloConfigService(): void
    {
        $configService = Mockery::mock(WhatsappConfigService::class);
        $configService->shouldReceive('resolveContactData')->once()->andReturn([
            'nome_exibicao' => 'Acme',
            'telefone_contato' => '',
            'endereco_completo' => '',
            'texto_assinatura' => '*Equipe Acme*',
        ]);
        $configService->shouldReceive('getTemplateCorpo')->once()->andReturn('Custom {{nome_destinatario}}');

        $factory = new WhatsappMessageFactory($configService, new WhatsappTemplateRenderer());

        $mensagem = $factory->render(
            TipoMensagemWhatsapp::RecrutamentoSelecao,
            100,
            ['nome_destinatario' => 'Ana']
        );

        $this->assertStringStartsWith('Custom Ana', $mensagem);
        $this->assertStringContainsString('MyBP', $mensagem);
    }
}
