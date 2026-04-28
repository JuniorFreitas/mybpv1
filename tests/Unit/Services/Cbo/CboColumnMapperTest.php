<?php

namespace Tests\Unit\Services\Cbo;

use App\Services\Cbo\CboColumnMapper;
use Tests\TestCase;

class CboColumnMapperTest extends TestCase
{
    public function testMapOcupacaoDetectaCabecalhosAlternativos(): void
    {
        $mapper = new CboColumnMapper();
        $headers = [0 => 'cod_ocupacao', 1 => 'descricao_ocupacao', 2 => 'cod_familia'];
        $m = $mapper->mapOcupacaoColumns($headers);
        $this->assertSame(0, $m['codigo']);
        $this->assertSame(1, $m['titulo']);
        $this->assertSame(2, $m['familia']);
    }

    public function testDerivaFamiliaPelosQuatroPrimeirosDigitos(): void
    {
        $mapper = new CboColumnMapper();
        $this->assertSame('3171', $mapper->deriveCodigoFamiliaFromOcupacao('317110'));
    }

    public function testMapPerfilDetectaColunas(): void
    {
        $mapper = new CboColumnMapper();
        $headers = [0 => 'cod_familia', 1 => 'perfil_ocupacional'];
        $m = $mapper->mapPerfilColumns($headers);
        $this->assertSame(0, $m['codigo_familia']);
        $this->assertSame(1, $m['descricao_sumaria']);
    }
}
