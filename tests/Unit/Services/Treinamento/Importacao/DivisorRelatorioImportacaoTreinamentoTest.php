<?php

namespace Tests\Unit\Services\Treinamento\Importacao;

use App\Services\Treinamento\Importacao\DivisorRelatorioImportacaoTreinamento;
use Tests\TestCase;

class DivisorRelatorioImportacaoTreinamentoTest extends TestCase
{
    public function testDividirSeparaSucessoEErro(): void
    {
        $dir = storage_path('app/test_divisor_' . uniqid());
        mkdir($dir, 0755, true);
        $origem = $dir . '/completo.csv';
        file_put_contents(
            $origem,
            "\xEF\xBB\xBFNome;CPF;Treinamento;Data Treinamento;Data Vencimento;Status;Mensagem;ID Treinamento\n"
            . "A;1;T1;01/01/2026;01/01/2027;SUCESSO;ok;10\n"
            . "B;2;T2;01/01/2026;01/01/2027;ERRO;falhou;\n"
            . "C;3;T3;01/01/2026;01/01/2027;SUCESSO;ok;11\n"
        );

        $divisor = new DivisorRelatorioImportacaoTreinamento();
        $resultado = $divisor->dividir($origem, $dir, 'uuid-x');

        $this->assertSame(3, $resultado['total']);
        $this->assertSame(2, $resultado['sucesso_count']);
        $this->assertSame(1, $resultado['erros_count']);
        $this->assertNotNull($resultado['sucesso']);
        $this->assertNotNull($resultado['erro']);
        $this->assertFileExists($resultado['sucesso']);
        $this->assertFileExists($resultado['erro']);

        $sucesso = file_get_contents($resultado['sucesso']);
        $erro = file_get_contents($resultado['erro']);
        $this->assertStringContainsString('SUCESSO', $sucesso);
        $this->assertStringNotContainsString(';ERRO;', $sucesso);
        $this->assertStringContainsString('ERRO', $erro);
        $this->assertStringNotContainsString(';SUCESSO;', $erro);

        @unlink($resultado['sucesso']);
        @unlink($resultado['erro']);
        @unlink($origem);
        @rmdir($dir);
    }
}
