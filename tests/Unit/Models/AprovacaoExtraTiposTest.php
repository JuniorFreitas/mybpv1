<?php

namespace Tests\Unit\Models;

use App\Models\AprovacaoExtraConfig;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AprovacaoExtraTiposTest extends TestCase
{
    #[Test]
    public function inclui_mudanca_cargo_e_demais_movimentacoes(): void
    {
        $tipos = AprovacaoExtraConfig::TIPOS_PROCESSO;

        $this->assertArrayHasKey(AprovacaoExtraConfig::TIPO_MUDANCA_CARGO, $tipos);
        $this->assertArrayHasKey(AprovacaoExtraConfig::TIPO_DEMISSAO, $tipos);
        $this->assertArrayHasKey(AprovacaoExtraConfig::TIPO_FERIAS, $tipos);
        $this->assertSame('mudanca_cargo', AprovacaoExtraConfig::TIPO_MUDANCA_CARGO);
    }
}
