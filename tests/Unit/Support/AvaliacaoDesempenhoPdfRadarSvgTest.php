<?php

namespace Tests\Unit\Support;

use App\Support\AvaliacaoDesempenhoPdfRadarSvg;
use PHPUnit\Framework\TestCase;

class AvaliacaoDesempenhoPdfRadarSvgTest extends TestCase
{
    public function test_charts_from_dados_emits_svg_com_poligono(): void
    {
        $dados = [
            'resultChart' => [
                ['name' => 'Liderança', 'data' => ['labels' => [1, 2, 3], 'datasets' => [['data' => [3, 4, 5]]]]],
            ],
            'result_topico' => collect([
                ['topico_pai' => 'Liderança', 'subtopico' => 'A', 'media_redonda' => 3, 'media' => 3.0],
                ['topico_pai' => 'Liderança', 'subtopico' => 'B', 'media_redonda' => 4, 'media' => 4.0],
                ['topico_pai' => 'Liderança', 'subtopico' => 'C', 'media_redonda' => 5, 'media' => 5.0],
            ]),
        ];

        $charts = AvaliacaoDesempenhoPdfRadarSvg::chartsFromDados($dados);
        $this->assertCount(1, $charts);
        $this->assertSame('Liderança', $charts[0]['name']);
        $this->assertSame(340, $charts[0]['img_w']);
        $this->assertStringContainsString('<svg', $charts[0]['svg']);
        $this->assertStringContainsString('polygon', $charts[0]['svg']);
        $this->assertStringContainsString('>A</text>', $charts[0]['svg']);
    }

    public function test_poucos_pontos_usa_barras(): void
    {
        $dados = [
            'resultChart' => [
                ['name' => 'G', 'data' => ['datasets' => [['data' => [2, 3]]]]],
            ],
            'result_topico' => collect([
                ['topico_pai' => 'G', 'subtopico' => 'X', 'media_redonda' => 2],
                ['topico_pai' => 'G', 'subtopico' => 'Y', 'media_redonda' => 3],
            ]),
        ];
        $charts = AvaliacaoDesempenhoPdfRadarSvg::chartsFromDados($dados);
        $this->assertSame(320, $charts[0]['img_w']);
        $this->assertStringContainsString('<rect', $charts[0]['svg']);
    }
}
