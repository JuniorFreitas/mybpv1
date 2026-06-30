<?php

namespace Tests\Unit\Domain\Whatsapp;

use App\Domain\Whatsapp\Services\WhatsappTemplateRenderer;
use Tests\TestCase;

class WhatsappTemplateRendererTest extends TestCase
{
    private WhatsappTemplateRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new WhatsappTemplateRenderer();
    }

    public function testSubstituiPlaceholders(): void
    {
        $resultado = $this->renderer->render(
            'Olá {{nome_destinatario}}, empresa {{empresa_nome}}',
            ['nome_destinatario' => 'João', 'empresa_nome' => 'Acme']
        );

        $this->assertSame('Olá João, empresa Acme', $resultado);
    }

    public function testPlaceholderAusenteRetornaNaoInformado(): void
    {
        $resultado = $this->renderer->render('Rota: {{rota}}', []);

        $this->assertSame('Rota: Não informado', $resultado);
    }

    public function testValorVazioExplicitoPermaneceVazio(): void
    {
        $resultado = $this->renderer->render("Linha\n{{observacao}}Fim", ['observacao' => '']);

        $this->assertSame("Linha\nFim", $resultado);
    }

    public function testNullRetornaNaoInformado(): void
    {
        $resultado = $this->renderer->render('{{campo}}', ['campo' => null]);

        $this->assertSame('Não informado', $resultado);
    }
}
