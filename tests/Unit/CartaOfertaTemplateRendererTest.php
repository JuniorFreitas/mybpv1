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
}
