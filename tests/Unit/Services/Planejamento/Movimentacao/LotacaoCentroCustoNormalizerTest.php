<?php

namespace Tests\Unit\Services\Planejamento\Movimentacao;

use App\Services\Planejamento\Movimentacao\LotacaoCentroCustoNormalizer;
use Tests\TestCase;

class LotacaoCentroCustoNormalizerTest extends TestCase
{
    public function test_sem_centro_de_custo_limpa_filial(): void
    {
        $lotacao = (new LotacaoCentroCustoNormalizer())->normalizar(null, 9, [9]);

        $this->assertSame([
            'centro_custo_id' => null,
            'filial' => false,
            'centro_custo_filial_id' => null,
        ], $lotacao);
    }

    public function test_centro_de_custo_sem_filial_nao_exige_id(): void
    {
        $lotacao = (new LotacaoCentroCustoNormalizer())->normalizar(15, null, []);

        $this->assertFalse($lotacao['filial']);
        $this->assertNull($lotacao['centro_custo_filial_id']);
        $this->assertSame(15, $lotacao['centro_custo_id']);
    }

    public function test_preenche_filial_a_partir_do_centro_de_custo_quando_o_id_nao_vem_no_payload(): void
    {
        $lotacao = (new LotacaoCentroCustoNormalizer())->normalizar(15, '', [42, 43]);

        $this->assertTrue($lotacao['filial']);
        $this->assertSame(42, $lotacao['centro_custo_filial_id']);
    }

    public function test_mantem_filial_informada_quando_pertence_ao_centro_de_custo(): void
    {
        $lotacao = (new LotacaoCentroCustoNormalizer())->normalizar(15, '43', [42, 43]);

        $this->assertSame(43, $lotacao['centro_custo_filial_id']);
    }

    public function test_aplica_a_mesma_regra_nos_campos_de_novo_centro_de_custo(): void
    {
        $dados = (new LotacaoCentroCustoNormalizer())->aplicarComFiliais(
            [
                'novo_centro_custo_id' => 8,
                'novo_filial' => true,
                'novo_centro_custo_filial_id' => '',
            ],
            [77],
            'novo_centro_custo_id',
            'novo_filial',
            'novo_centro_custo_filial_id'
        );

        $this->assertTrue($dados['novo_filial']);
        $this->assertSame(77, $dados['novo_centro_custo_filial_id']);
        $this->assertSame(8, $dados['novo_centro_custo_id']);
    }

    public function test_aplica_a_mesma_regra_nos_campos_anteriores(): void
    {
        $dados = (new LotacaoCentroCustoNormalizer())->aplicarComFiliais(
            [
                'anterior_centro_custo_id' => 3,
                'anterior_filial' => true,
                'anterior_centro_custo_filial_id' => null,
            ],
            [12],
            'anterior_centro_custo_id',
            'anterior_filial',
            'anterior_centro_custo_filial_id'
        );

        $this->assertTrue($dados['anterior_filial']);
        $this->assertSame(12, $dados['anterior_centro_custo_filial_id']);
    }
}
