<?php

namespace Tests\Unit\Services\Lotacao;

use App\Services\Lotacao\LotacaoDoCentroCusto;
use Tests\TestCase;

class LotacaoDoCentroCustoTest extends TestCase
{
    public function test_sem_centro_de_custo_nao_define_filial(): void
    {
        $lotacao = (new LotacaoDoCentroCusto())->aPartirDoCentro(null, 10);

        $this->assertSame([
            'centro_custo_id' => null,
            'filial' => false,
            'centro_custo_filial_id' => null,
        ], $lotacao);
    }

    public function test_sem_empresa_nao_define_filial(): void
    {
        $lotacao = (new LotacaoDoCentroCusto())->aPartirDoCentro(15, 0);

        $this->assertNull($lotacao['centro_custo_id']);
        $this->assertFalse($lotacao['filial']);
    }
}
