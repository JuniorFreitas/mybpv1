<?php

namespace Tests\Unit\Models;

use App\Models\Admissao;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdmissaoDataAsoMutatorTest extends TestCase
{
    #[Test]
    public function data_aso_valida_e_persistida(): void
    {
        $admissao = new Admissao();
        $admissao->data_aso = '15/01/2024';

        $this->assertNotNull($admissao->getAttributes()['data_aso'] ?? null);
        $this->assertNotSame('', $admissao->getAttributes()['data_aso']);
    }

    #[Test]
    public function data_aso_nula_permanece_nula(): void
    {
        $admissao = new Admissao();
        $admissao->data_aso = null;

        $this->assertNull($admissao->getAttributes()['data_aso'] ?? null);
    }

    #[Test]
    public function data_aso_placeholder_invalido_vira_nulo(): void
    {
        $admissao = new Admissao();
        $admissao->data_aso = 'aaaa-mm-dd';

        $this->assertNull($admissao->getAttributes()['data_aso'] ?? null);
    }
}
