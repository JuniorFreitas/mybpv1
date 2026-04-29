<?php

namespace Tests\Feature;

use Tests\TestCase;

class TreinamentoCarteiraPdfTest extends TestCase
{
    public function test_fontes_carteira_existem_no_public(): void
    {
        $this->assertFileExists(public_path('fonts/carteira/noto-sans-400.woff2'));
        $this->assertFileExists(public_path('fonts/carteira/noto-sans-700.woff2'));
        $this->assertFileExists(public_path('fonts/carteira/sacramento-400.woff2'));
    }

    public function test_layout_carteira_define_escala_e_fontes_locais(): void
    {
        $layout = file_get_contents(resource_path('views/pdf/treinamento/carteira/layout_carteira.blade.php'));
        $this->assertStringContainsString('.etiqueta-bloqueio-par', $layout);
        $this->assertStringContainsString('.etiqueta-bloqueio-dupla-folha', $layout);
        $this->assertStringContainsString('etiqueta-bloqueio-dupla-folha--duas-linhas', $layout);
        $this->assertStringContainsString('align-items: stretch', $layout);
        $this->assertStringContainsString('--font-scale-sm', $layout);
        $this->assertStringContainsString('@font-face', $layout);
        $this->assertStringNotContainsString('fonts.googleapis.com', $layout);
    }

    public function test_cart_bloqueio_agrupa_duas_etiquetas_por_folha(): void
    {
        $bloqueio = file_get_contents(resource_path('views/pdf/treinamento/carteira/cart_bloqueio.blade.php'));
        $this->assertStringContainsString('chunk(2)', $bloqueio);
        $this->assertStringContainsString('etiqueta-bloqueio-dupla-folha', $bloqueio);
        $this->assertStringContainsString('etiqueta-bloqueio-dupla-folha--duas-linhas', $bloqueio);
        $this->assertStringContainsString('--bloqueio-altura-linha-par', $bloqueio);
    }
}
