<?php

namespace Tests\Unit;

use App\Services\CartaOferta\CartaOfertaTemplateRenderer;
use Tests\TestCase;

class CartaOfertaTemplateRendererTest extends TestCase
{
    public function testRenderSubstituiPlaceholders(): void
    {
        $renderer = new CartaOfertaTemplateRenderer();
        $html = '<p>Ola {{colaborador.nome}} - {{cargo}}</p>';
        $dados = [
            'colaborador' => ['nome' => 'Maria'],
            'cargo' => 'Analista'
        ];

        $resultado = $renderer->render($html, $dados);

        $this->assertSame('<p>Ola Maria - Analista</p>', $resultado);
    }

    public function testRenderRemoveTagsNaoPermitidas(): void
    {
        $renderer = new CartaOfertaTemplateRenderer();
        $html = '<p>Teste</p><script>alert(1)</script>';

        $resultado = $renderer->render($html, []);

        $this->assertSame('<p>Teste</p>alert(1)', $resultado);
    }

    public function testRenderSubstituiPlaceholderInexistentePorVazio(): void
    {
        $renderer = new CartaOfertaTemplateRenderer();
        $html = '<p>{{colaborador.nome}}</p>';

        $resultado = $renderer->render($html, []);

        $this->assertSame('<p></p>', $resultado);
    }

    public function testRenderPreservaAlinhamentoTexto(): void
    {
        $renderer = new CartaOfertaTemplateRenderer();
        $html = '<p style="text-align: center; color: red; background: url(x)">Titulo</p>';

        $resultado = $renderer->render($html, []);

        $this->assertStringContainsString('text-align: center', $resultado);
        $this->assertStringNotContainsString('color', $resultado);
        $this->assertStringNotContainsString('url(', $resultado);
    }

    public function testRenderPreservaEspacamentoSeguro(): void
    {
        $renderer = new CartaOfertaTemplateRenderer();
        $html = '<p style="text-align: justify; line-height: 22pt; margin-bottom: 1cm;">Texto</p>';

        $resultado = $renderer->render($html, []);

        $this->assertStringContainsString('text-align: justify', $resultado);
        $this->assertStringContainsString('line-height: 22pt', $resultado);
        $this->assertStringContainsString('margin-bottom: 1cm', $resultado);
    }

    public function testRenderRemoveLinhaSalarioVazia(): void
    {
        $renderer = new CartaOfertaTemplateRenderer();
        $html = '<p>Intro</p><p>Salário: <strong>{{salario}}</strong></p><p>Fim</p>';

        $resultado = $renderer->render($html, ['salario' => '']);

        $this->assertStringContainsString('<p>Intro</p>', $resultado);
        $this->assertStringContainsString('<p>Fim</p>', $resultado);
        $this->assertStringNotContainsString('Salário', $resultado);
    }

    public function testRenderInjetaBlocoSalarioHtml(): void
    {
        $renderer = new CartaOfertaTemplateRenderer();
        $html = '<p>Intro</p>{{bloco_salario}}<p>Fim</p>';
        $bloco = '<p style="line-height: 22pt;">Salário: <strong>R$ 1.500,00</strong></p>';

        $resultado = $renderer->render($html, ['bloco_salario' => $bloco]);

        $this->assertStringContainsString('R$ 1.500,00', $resultado);
        $this->assertStringContainsString('Salário', $resultado);
    }
}
