<?php

namespace Tests\Unit\Services\Treinamento;

use App\Services\Treinamento\CarteiraMontagem;
use PHPUnit\Framework\TestCase;

class CarteiraMontagemTest extends TestCase
{
    public function test_chapa_usa_numero_do_cracha(): void
    {
        $config = CarteiraMontagem::aplicarNoConfig([], [
            'numero_cracha' => '1234',
            'matricula' => '9988',
        ]);

        $this->assertSame('chapa', $config['identificador']);
        $this->assertSame('Chapa', $config['identificador_label']);
        $this->assertSame('1234', $config['identificador_valor']);
        $this->assertSame('Chapa', $config['identificador_etiqueta_label']);
        $this->assertSame('1234', $config['identificador_etiqueta_valor']);
        $this->assertSame(CarteiraMontagem::VIEW_CARTEIRA, $config['blade_carteira']);
        $this->assertSame(CarteiraMontagem::VIEW_ETIQUETA, $config['blade_etiqueta']);
    }

    public function test_matricula_usa_campo_da_admissao(): void
    {
        $config = CarteiraMontagem::aplicarNoConfig([
            'identificador' => 'matricula',
            'identificador_etiqueta' => 'matricula',
        ], [
            'numero_cracha' => '1234',
            'matricula' => '9988',
        ]);

        $this->assertSame('Matr', $config['identificador_label']);
        $this->assertSame('9988', $config['identificador_valor']);
        $this->assertSame('Matr', $config['identificador_etiqueta_label']);
        $this->assertSame('9988', $config['identificador_etiqueta_valor']);
    }

    public function test_agrupa_consecutivos_pela_mesma_view(): void
    {
        $grupos = CarteiraMontagem::agruparPorView([
            ['segmento_config' => ['blade_carteira' => 'pdf.treinamento.carteira.cart_treinamento']],
            ['segmento_config' => ['blade_carteira' => 'pdf.treinamento.carteira.cart_treinamento']],
            ['segmento_config' => ['blade_carteira' => 'pdf.treinamento.carteira.cart_treinamento_coimbra']],
        ], 'blade_carteira', CarteiraMontagem::VIEW_CARTEIRA);

        $this->assertCount(2, $grupos);
        $this->assertCount(2, $grupos[0]['itens']);
        $this->assertSame('pdf.treinamento.carteira.cart_treinamento_coimbra', $grupos[1]['view']);
    }
}
