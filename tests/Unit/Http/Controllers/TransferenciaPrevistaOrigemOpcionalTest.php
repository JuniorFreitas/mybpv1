<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\TransferenciaPrevistaController;
use ReflectionMethod;
use Tests\TestCase;

class TransferenciaPrevistaOrigemOpcionalTest extends TestCase
{
    public function test_normalizar_centro_custo_origem_id_converte_vazio_para_null(): void
    {
        $controller = new TransferenciaPrevistaController();
        $method = new ReflectionMethod(TransferenciaPrevistaController::class, 'normalizarCentroCustoOrigemId');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($controller, null));
        $this->assertNull($method->invoke($controller, ''));
        $this->assertSame(12, $method->invoke($controller, '12'));
        $this->assertSame(3, $method->invoke($controller, 3));
    }
}
