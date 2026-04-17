<?php

namespace Tests\Unit\Support;

use App\Support\AvaliacaoDesempenhoPdfViewData;
use PHPUnit\Framework\TestCase;

class AvaliacaoDesempenhoPdfViewDataTest extends TestCase
{
    public function test_normalizar_auto_avaliacao(): void
    {
        $this->assertSame('AUTOAVALIAÇÃO', AvaliacaoDesempenhoPdfViewData::normalizarTituloEtapa('auto avaliacao'));
        $this->assertSame('AUTOAVALIAÇÃO', AvaliacaoDesempenhoPdfViewData::normalizarTituloEtapa('Auto Avaliação'));
    }

    public function test_comentarios_ordenados_coloca_auto_avaliacao_primeiro(): void
    {
        $dados = [
            'fluxo_etapas' => [
                ['label' => 'Avaliador 1'],
                ['label' => 'Avaliador 2'],
            ],
            'result_topico_pai_agrupado' => [
                [
                    [
                        'topico_pai' => 'Grupo',
                        'avaliadores' => [
                            ['tipo' => 'Avaliador Par', 'origem' => 'Avaliador', 'comentario' => 'Segundo'],
                            ['tipo' => 'Autoavaliação', 'origem' => 'Funcionario', 'comentario' => 'Primeiro'],
                        ],
                    ],
                ],
            ],
        ];

        $ordenados = AvaliacaoDesempenhoPdfViewData::comentariosOrdenados($dados);
        $this->assertCount(2, $ordenados);
        $this->assertStringContainsString('Primeiro', (string) ($ordenados[0]['avaliador']['comentario'] ?? ''));
    }

    public function test_ordenar_dados_pdf_por_topico_id_e_ordem_graficos(): void
    {
        $dados = [
            'fluxo_etapas' => [],
            'result_topico' => collect([
                ['topico_id' => 20, 'topico_pai_id' => 2, 'topico_pai' => 'Comp B', 'subtopico' => 'c2', 'avaliadores' => [['tipo' => 'X']]],
                ['topico_id' => 10, 'topico_pai_id' => 1, 'topico_pai' => 'Comp A', 'subtopico' => 'c1', 'avaliadores' => [['tipo' => 'X']]],
                ['topico_id' => 30, 'topico_pai_id' => 1, 'topico_pai' => 'Comp A', 'subtopico' => 'c3', 'avaliadores' => [['tipo' => 'X']]],
            ]),
            'result_topico_pai_agrupado' => [],
            'resultChart' => [
                ['name' => 'Comp B', 'data' => []],
                ['name' => 'Comp A', 'data' => []],
            ],
        ];

        $out = AvaliacaoDesempenhoPdfViewData::ordenarDadosParaPdf($dados);

        $this->assertSame([10, 20, 30], $out['result_topico']->pluck('topico_id')->all());
        $this->assertCount(2, $out['result_topico_pai_agrupado']);
        $g0 = collect($out['result_topico_pai_agrupado'][0]);
        $this->assertSame(['c1', 'c3'], $g0->pluck('subtopico')->all());
        $this->assertSame(['Comp A', 'Comp B'], array_column($out['resultChart'], 'name'));
    }
}
