<?php

namespace Tests\Unit;

use App\Services\Aniversariante\AniversarianteMensagemRenderer;
use Tests\TestCase;

class AniversarianteMensagemRendererTest extends TestCase
{
    public function testRenderSubstituiNomeEEmpresa(): void
    {
        $renderer = new AniversarianteMensagemRenderer();
        $html = '<p>Parabéns {{nome}} — {{empresa}}</p>';

        $resultado = $renderer->render($html, [
            'nome' => 'Maria Silva',
            'empresa' => 'ACME RH',
        ]);

        $this->assertSame('<p>Parabéns Maria Silva — ACME RH</p>', $resultado);
    }

    public function testRenderRemoveTagsNaoPermitidas(): void
    {
        $renderer = new AniversarianteMensagemRenderer();
        $html = '<p>Olá {{nome}}</p><script>alert(1)</script>';

        $resultado = $renderer->render($html, ['nome' => 'João']);

        $this->assertSame('<p>Olá João</p>alert(1)', $resultado);
    }

    public function testRenderSubstituiPlaceholderInexistentePorVazio(): void
    {
        $renderer = new AniversarianteMensagemRenderer();
        $html = '<p>{{nome}} {{inexistente}}</p>';

        $resultado = $renderer->render($html, ['nome' => 'Ana']);

        $this->assertSame('<p>Ana </p>', $resultado);
    }
}
