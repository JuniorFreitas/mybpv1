<?php

namespace Tests\Unit\Services\CentroCusto;

use App\Services\CentroCusto\CentroCustoCnpjSyncService;
use Tests\TestCase;

class CentroCustoCnpjSyncServiceTest extends TestCase
{
    public function test_eh_cnpj_matriz_quando_info_indica_matriz(): void
    {
        $resultado = (new CentroCustoCnpjSyncService())->ehCnpjMatriz(
            '15110791000180',
            111969,
            ['matriz' => true]
        );

        $this->assertTrue($resultado);
    }

    public function test_eh_cnpj_matriz_false_quando_info_e_filial(): void
    {
        $resultado = (new CentroCustoCnpjSyncService())->ehCnpjMatriz(
            '28214639000199',
            111969,
            ['matriz' => false]
        );

        $this->assertFalse($resultado);
    }
}
