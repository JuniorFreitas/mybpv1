<?php

namespace Tests\Unit\Support;

use App\Models\AvaliacaoAvaliadoresTipos;
use App\Models\AvaliacaoFeedback;
use App\Support\AvaliacaoDesempenhoPdfViewData;
use Illuminate\Support\Collection;
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

    public function test_titulo_etapa_coluna_pdf_espelha_modal_sem_caixa_alta_forcada(): void
    {
        $fluxo = [['label' => 'Avaliador par']];
        $this->assertSame(
            'Gestor direto',
            AvaliacaoDesempenhoPdfViewData::tituloEtapaFluxoColunaPdf(0, ['tipo' => 'Gestor direto', 'origem' => 'Avaliador'], $fluxo)
        );
        $this->assertSame(
            'Avaliador par',
            AvaliacaoDesempenhoPdfViewData::tituloEtapaFluxoColunaPdf(0, ['origem' => 'Avaliador'], $fluxo)
        );
        $this->assertSame(
            'Autoavaliação',
            AvaliacaoDesempenhoPdfViewData::tituloEtapaFluxoColunaPdf(0, ['origem' => 'Funcionario'], [])
        );
        $this->assertSame('Avaliador 2', AvaliacaoDesempenhoPdfViewData::tituloEtapaFluxoColunaPdf(1, ['origem' => 'Avaliador'], []));
    }

    public function test_indice_etapa_fluxo_pdf_prioriza_avaliacao_tipo_id(): void
    {
        $fluxo = [
            ['id' => 0, 'label' => 'Auto Avaliação', 'principal' => false],
            ['id' => 10, 'label' => 'Gestor direto (Avaliador Final)', 'principal' => true],
        ];
        $av = [
            'tipo' => 'Gestor',
            'origem' => AvaliacaoFeedback::ORIGEM_AVALIADOR,
            'avaliacao_tipo_id' => 10,
        ];
        $this->assertSame(1, AvaliacaoDesempenhoPdfViewData::indiceEtapaNoFluxoPdf($av, $fluxo));
        $avSemId = ['tipo' => 'Gestor', 'origem' => AvaliacaoFeedback::ORIGEM_AVALIADOR];
        $this->assertNull(AvaliacaoDesempenhoPdfViewData::indiceEtapaNoFluxoPdf($avSemId, $fluxo));
    }

    public function test_ordenar_feedbacks_por_fluxo_respeita_ordem_das_etapas(): void
    {
        $fluxo = [
            ['id' => 0, 'label' => 'Auto Avaliação', 'principal' => false],
            ['id' => 2, 'label' => 'Avaliador Par', 'principal' => false],
            ['id' => 1, 'label' => 'Gestor direto (Avaliador Final)', 'principal' => true],
        ];

        $tipoPar = new AvaliacaoAvaliadoresTipos(['label' => 'Avaliador Par']);
        $tipoGestor = new AvaliacaoAvaliadoresTipos(['label' => 'Gestor direto']);

        $gestorFinal = new AvaliacaoFeedback([
            'origem_feedback' => AvaliacaoFeedback::ORIGEM_AVALIADOR,
            'principal' => true,
            'avaliacao_tipo_id' => 1,
        ]);
        $gestorFinal->setRelation('TipoAvaliador', $tipoGestor);

        $par = new AvaliacaoFeedback([
            'origem_feedback' => AvaliacaoFeedback::ORIGEM_AVALIADOR,
            'principal' => false,
            'avaliacao_tipo_id' => 2,
        ]);
        $par->setRelation('TipoAvaliador', $tipoPar);

        $auto = new AvaliacaoFeedback([
            'origem_feedback' => AvaliacaoFeedback::ORIGEM_FUNCIONARIO,
            'principal' => false,
            'avaliacao_tipo_id' => null,
        ]);
        $auto->setRelation('TipoAvaliador', null);

        $embaralhado = new Collection([$gestorFinal, $par, $auto]);
        $ordenado = AvaliacaoDesempenhoPdfViewData::ordenarFeedbacksPorFluxo($embaralhado, $fluxo)->values();

        $this->assertSame($auto, $ordenado[0]);
        $this->assertSame($par, $ordenado[1]);
        $this->assertSame($gestorFinal, $ordenado[2]);
    }

    public function test_itens_escala_informativo_pdf_cinco_niveis_e_texto_nivel_5(): void
    {
        $itens = AvaliacaoDesempenhoPdfViewData::itensEscalaInformativoDesempenhoPdf();
        $this->assertCount(5, $itens);
        $this->assertSame(5, $itens[0]['nota']);
        $this->assertStringContainsString('Superou muito as expectativas', $itens[0]['texto']);
        $this->assertSame(1, $itens[4]['nota']);
        $this->assertStringContainsString('Muito abaixo das expectativas', $itens[4]['texto']);
    }

    public function test_formatar_decimal_e_texto_nota_igual_modal_pdi(): void
    {
        $this->assertSame('3.5', AvaliacaoDesempenhoPdfViewData::formatarDecimalNotaPdf(3.5));
        $this->assertSame('0.0', AvaliacaoDesempenhoPdfViewData::formatarDecimalNotaPdf(null));
        $this->assertSame('Atingiu', AvaliacaoDesempenhoPdfViewData::textoNotaResultadoPdf(3));
        $this->assertSame('ATINGIU', AvaliacaoDesempenhoPdfViewData::textoNotaResultadoMaiusculoPdf(3));
        $this->assertSame('Sem nota', AvaliacaoDesempenhoPdfViewData::textoNotaResultadoPdf(99));
        $this->assertSame('3', AvaliacaoDesempenhoPdfViewData::sufixoClasseNotaResultadoPdf(2.6));
        $this->assertSame('neutro', AvaliacaoDesempenhoPdfViewData::sufixoClasseNotaResultadoPdf(''));
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
