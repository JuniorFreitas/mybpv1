<?php

namespace Tests\Unit\Services\CentroCusto;

use App\Services\CentroCusto\CentroCustoCnpjSyncService;
use Tests\TestCase;

class CentroCustoCnpjSyncServiceTest extends TestCase
{
    public function test_eh_cnpj_matriz_quando_info_indica_matriz(): void
    {
        $method = (new \ReflectionClass(CentroCustoCnpjSyncService::class))->getMethod('ehCnpjMatriz');
        $method->setAccessible(true);

        $resultado = $method->invoke(
            new CentroCustoCnpjSyncService(),
            '15110791000180',
            111969,
            ['matriz' => true]
        );

        $this->assertTrue($resultado);
    }

    public function test_eh_cnpj_matriz_false_quando_info_e_filial(): void
    {
        $method = (new \ReflectionClass(CentroCustoCnpjSyncService::class))->getMethod('ehCnpjMatriz');
        $method->setAccessible(true);

        $resultado = $method->invoke(
            new CentroCustoCnpjSyncService(),
            '28214639000199',
            111969,
            ['matriz' => false]
        );

        $this->assertFalse($resultado);
    }
}
