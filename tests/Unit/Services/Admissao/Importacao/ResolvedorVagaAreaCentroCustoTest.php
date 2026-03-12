<?php

namespace Tests\Unit\Services\Admissao\Importacao;

use App\Services\Admissao\Importacao\ResolvedorVagaAreaCentroCusto;
use Tests\TestCase;

/**
 * Testes do Resolvedor sem dependência de banco (callables injetados).
 * Para testes de integração com DB, usar RefreshDatabase em ambiente com migrations aplicadas.
 */
class ResolvedorVagaAreaCentroCustoTest extends TestCase
{
    private int $empresaId = 1;

    public function testResolveCodVagaVazioRetornaErro(): void
    {
        $resolvedor = new ResolvedorVagaAreaCentroCusto();
        $resultado = $resolvedor->resolverVaga($this->empresaId, '');
        $this->assertNull($resultado['id']);
        $this->assertSame('Código ou nome da vaga é obrigatório.', $resultado['erro']);
    }

    public function testResolveCodVagaPorIdNumericoComMock(): void
    {
        $resolvedor = new ResolvedorVagaAreaCentroCusto(
            function (int $empresaId, string $valor) {
                $this->assertSame(1, $empresaId);
                $this->assertSame('42', $valor);
                return ['id' => 42, 'erro' => null];
            },
            null,
            null
        );
        $resultado = $resolvedor->resolverVaga($this->empresaId, '42');
        $this->assertSame(42, $resultado['id']);
        $this->assertNull($resultado['erro']);
    }

    public function testResolveCodVagaPorNomeComMock(): void
    {
        $resolvedor = new ResolvedorVagaAreaCentroCusto(
            function (int $empresaId, string $valor) {
                $this->assertSame('Motorista', $valor);
                return ['id' => 10, 'erro' => null];
            },
            null,
            null
        );
        $resultado = $resolvedor->resolverVaga($this->empresaId, 'Motorista');
        $this->assertSame(10, $resultado['id']);
        $this->assertNull($resultado['erro']);
    }

    public function testResolverVagaRetornaErroQuandoMockRetornaErro(): void
    {
        $resolvedor = new ResolvedorVagaAreaCentroCusto(
            function () {
                return ['id' => null, 'erro' => 'Nenhuma vaga encontrada com o nome informado.'];
            },
            null,
            null
        );
        $resultado = $resolvedor->resolverVaga($this->empresaId, 'VagaInexistente');
        $this->assertNotNull($resultado['erro']);
        $this->assertNull($resultado['id']);
    }

    public function testResolveCodAreaVazioRetornaNullSemErro(): void
    {
        $resolvedor = new ResolvedorVagaAreaCentroCusto();
        $resultado = $resolvedor->resolverArea($this->empresaId, '');
        $this->assertNull($resultado['id']);
        $this->assertNull($resultado['erro']);
    }

    public function testResolveCodAreaPorIdComMock(): void
    {
        $resolvedor = new ResolvedorVagaAreaCentroCusto(
            null,
            function (int $empresaId, string $valor) {
                $this->assertSame('5', $valor);
                return ['id' => 5, 'erro' => null];
            },
            null
        );
        $resultado = $resolvedor->resolverArea($this->empresaId, '5');
        $this->assertSame(5, $resultado['id']);
        $this->assertNull($resultado['erro']);
    }

    public function testResolveCodAreaPorLabelComMock(): void
    {
        $resolvedor = new ResolvedorVagaAreaCentroCusto(
            null,
            function (int $empresaId, string $valor) {
                $this->assertSame('Administrativo', $valor);
                return ['id' => 3, 'erro' => null];
            },
            null
        );
        $resultado = $resolvedor->resolverArea($this->empresaId, 'Administrativo');
        $this->assertSame(3, $resultado['id']);
        $this->assertNull($resultado['erro']);
    }

    public function testResolveCentroCustoVazioRetornaErro(): void
    {
        $resolvedor = new ResolvedorVagaAreaCentroCusto();
        $resultado = $resolvedor->resolverCentroCusto($this->empresaId, '');
        $this->assertNull($resultado['id']);
        $this->assertSame('Centro de custo é obrigatório.', $resultado['erro']);
    }

    public function testResolveCentroCustoNullRetornaErro(): void
    {
        $resolvedor = new ResolvedorVagaAreaCentroCusto();
        $resultado = $resolvedor->resolverCentroCusto($this->empresaId, null);
        $this->assertNull($resultado['id']);
        $this->assertSame('Centro de custo é obrigatório.', $resultado['erro']);
    }

    public function testResolveCentroCustoPorIdComMock(): void
    {
        $resolvedor = new ResolvedorVagaAreaCentroCusto(
            null,
            null,
            function (int $empresaId, string $valor) {
                $this->assertSame('7', $valor);
                return ['id' => 7, 'erro' => null];
            }
        );
        $resultado = $resolvedor->resolverCentroCusto($this->empresaId, '7');
        $this->assertSame(7, $resultado['id']);
        $this->assertNull($resultado['erro']);
    }

    public function testResolveCentroCustoPorLabelComMock(): void
    {
        $resolvedor = new ResolvedorVagaAreaCentroCusto(
            null,
            null,
            function (int $empresaId, string $valor) {
                $this->assertSame('Obras', $valor);
                return ['id' => 2, 'erro' => null];
            }
        );
        $resultado = $resolvedor->resolverCentroCusto($this->empresaId, 'Obras');
        $this->assertSame(2, $resultado['id']);
        $this->assertNull($resultado['erro']);
    }

    public function testResolveCentroCustoTrimValor(): void
    {
        $resolvedor = new ResolvedorVagaAreaCentroCusto(
            null,
            null,
            function (int $empresaId, string $valor) {
                $this->assertSame('1', $valor);
                return ['id' => 1, 'erro' => null];
            }
        );
        $resultado = $resolvedor->resolverCentroCusto($this->empresaId, '  1  ');
        $this->assertSame(1, $resultado['id']);
        $this->assertNull($resultado['erro']);
    }

    public function testEstruturaRetornoPossuiIdEErro(): void
    {
        $resolvedor = new ResolvedorVagaAreaCentroCusto();
        $resultado = $resolvedor->resolverCentroCusto($this->empresaId, '');
        $this->assertArrayHasKey('id', $resultado);
        $this->assertArrayHasKey('erro', $resultado);
    }
}
