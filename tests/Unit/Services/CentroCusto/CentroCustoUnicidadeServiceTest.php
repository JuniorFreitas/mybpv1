<?php

namespace Tests\Unit\Services\CentroCusto;

use App\Services\CentroCusto\CentroCustoCnpjSyncService;
use App\Services\CentroCusto\CentroCustoUnicidadeService;
use Illuminate\Validation\Rules\Unique;
use Tests\TestCase;

class CentroCustoUnicidadeServiceTest extends TestCase
{
    public function test_regra_label_retorna_unique_com_ignore(): void
    {
        $service = new CentroCustoUnicidadeService(new CentroCustoCnpjSyncService());

        $regra = $service->regraLabel([
            'label' => 'ADMINISTRATIVO',
            'ativo' => true,
        ], 15, 10, false);

        $this->assertInstanceOf(Unique::class, $regra);
    }

    public function test_regra_label_sem_ignore(): void
    {
        $service = new CentroCustoUnicidadeService(new CentroCustoCnpjSyncService());

        $regra = $service->regraLabel([
            'label' => 'OPERACIONAL',
            'ativo' => false,
        ], null, 10, false);

        $this->assertInstanceOf(Unique::class, $regra);
    }

    public function test_regra_label_para_ativacao_com_status_ativo(): void
    {
        $service = new CentroCustoUnicidadeService(new CentroCustoCnpjSyncService());

        $regra = $service->regraLabel([
            'label' => 'Corporativo RH',
            'ativo' => true,
            'campoCnpj' => null,
        ], 185, 63122, false);

        $this->assertInstanceOf(Unique::class, $regra);
    }
}
