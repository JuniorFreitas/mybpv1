<?php

namespace Tests\Unit\Services\Admissao\Importacao;

use App\Services\Admissao\Importacao\PersistidorAdmissaoImportada;
use Tests\TestCase;

/**
 * Testes do Persistidor sem dependência de banco (payload inválido retorna erro).
 * Testes de integração (criação/atualização por CPF) requerem DB migrado (RefreshDatabase/DatabaseTransactions).
 */
class PersistidorAdmissaoImportadaTest extends TestCase
{
    private PersistidorAdmissaoImportada $persistidor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->persistidor = new PersistidorAdmissaoImportada();
    }

    public function testPersistirComPayloadVazioRetornaErro(): void
    {
        $resultado = $this->persistidor->persistir([], 1, null);
        $this->assertFalse($resultado['sucesso']);
        $this->assertArrayHasKey('erro', $resultado);
        $this->assertNotEmpty($resultado['erro']);
    }

    public function testPersistirComPayloadIncompletoRetornaErro(): void
    {
        $item = [
            'curriculo' => ['cpf' => '123.456.789-09', 'nome' => 'Teste'],
            'admissao' => [],
        ];
        $resultado = $this->persistidor->persistir($item, 1, null);
        $this->assertFalse($resultado['sucesso']);
        $this->assertArrayHasKey('erro', $resultado);
    }

    public function testRetornoPossuiChaveSucesso(): void
    {
        $resultado = $this->persistidor->persistir([], 1, null);
        $this->assertArrayHasKey('sucesso', $resultado);
        $this->assertIsBool($resultado['sucesso']);
    }

    public function testRetornoDeErroPossuiChaveErro(): void
    {
        $resultado = $this->persistidor->persistir(['curriculo' => [], 'admissao' => []], 1, null);
        $this->assertFalse($resultado['sucesso']);
        $this->assertArrayHasKey('erro', $resultado);
        $this->assertIsString($resultado['erro']);
    }
}
