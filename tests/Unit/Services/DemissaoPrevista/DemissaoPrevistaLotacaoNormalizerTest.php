<?php

namespace Tests\Unit\Services\DemissaoPrevista;

use App\Services\DemissaoPrevista\DemissaoPrevistaLotacaoNormalizer;
use Tests\TestCase;

class DemissaoPrevistaLotacaoNormalizerTest extends TestCase
{
    public function test_sem_centro_de_custo_limpa_filial(): void
    {
        $lotacao = (new DemissaoPrevistaLotacaoNormalizer())->normalizar(null, 9, [9]);

        $this->assertSame([
            'centro_custo_id' => null,
            'filial' => false,
            'centro_custo_filial_id' => null,
        ], $lotacao);
    }

    public function test_centro_de_custo_sem_filial_nao_exige_id(): void
    {
        $lotacao = (new DemissaoPrevistaLotacaoNormalizer())->normalizar(15, null, []);

        $this->assertFalse($lotacao['filial']);
        $this->assertNull($lotacao['centro_custo_filial_id']);
        $this->assertSame(15, $lotacao['centro_custo_id']);
    }

    public function test_preenche_filial_a_partir_do_centro_de_custo_quando_o_id_nao_vem_no_payload(): void
    {
        $lotacao = (new DemissaoPrevistaLotacaoNormalizer())->normalizar(15, '', [42, 43]);

        $this->assertTrue($lotacao['filial']);
        $this->assertSame(42, $lotacao['centro_custo_filial_id']);
    }

    public function test_mantem_filial_informada_quando_pertence_ao_centro_de_custo(): void
    {
        $lotacao = (new DemissaoPrevistaLotacaoNormalizer())->normalizar(15, '43', [42, 43]);

        $this->assertSame(43, $lotacao['centro_custo_filial_id']);
    }
}
