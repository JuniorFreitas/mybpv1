<?php

namespace Tests\Unit\Services\Admissao\Importacao;

use App\Services\Admissao\Importacao\LeitorPlanilhaAdmissao;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Tests\TestCase;

class LeitorPlanilhaAdmissaoTest extends TestCase
{
    private string $tempPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempPath = storage_path('app/test_importacao_' . uniqid() . '.xlsx');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempPath)) {
            @unlink($this->tempPath);
        }
        parent::tearDown();
    }

    public function testLerRetornaChunksComCabecalhoMapeado(): void
    {
        $this->criarPlanilhaMinima(3);
        $leitor = new LeitorPlanilhaAdmissao();
        $chunks = iterator_to_array($leitor->ler($this->tempPath, 2));

        $this->assertCount(2, $chunks);
        $this->assertCount(2, $chunks[0]);
        $this->assertCount(1, $chunks[1]);

        $primeiraLinha = $chunks[0][0];
        $this->assertArrayHasKey('cpf', $primeiraLinha);
        $this->assertArrayHasKey('nome', $primeiraLinha);
        $this->assertSame('12345678901', $primeiraLinha['cpf']);
        $this->assertSame('Fulano', $primeiraLinha['nome']);
    }

    public function testLerConverteDataExcelParaDdMmYyyy(): void
    {
        $this->criarPlanilhaComDataExcel();
        $leitor = new LeitorPlanilhaAdmissao();
        $chunks = iterator_to_array($leitor->ler($this->tempPath, 10));

        $this->assertNotEmpty($chunks);
        $linha = $chunks[0][0];
        $this->assertArrayHasKey('data_admissao', $linha);
        $this->assertMatchesRegularExpression('/^\d{2}\/\d{2}\/\d{4}$/', $linha['data_admissao']);
    }

    public function testLerNormalizaCabecalhoComAsterisco(): void
    {
        $this->criarPlanilhaComCabecalhoAsterisco();
        $leitor = new LeitorPlanilhaAdmissao();
        $chunks = iterator_to_array($leitor->ler($this->tempPath, 5));

        $this->assertNotEmpty($chunks);
        $linha = $chunks[0][0];
        $this->assertArrayHasKey('cpf', $linha);
        $this->assertArrayNotHasKey('cpf*', $linha);
    }

    public function testLerArquivoInexistenteLancaExcecao(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('não encontrado');

        $leitor = new LeitorPlanilhaAdmissao();
        iterator_to_array($leitor->ler('arquivo_que_nao_existe.xlsx', 10));
    }

    public function testLerPlanilhaVaziaRetornaIteratorVazio(): void
    {
        $this->criarPlanilhaMinima(0);
        $leitor = new LeitorPlanilhaAdmissao();
        $chunks = iterator_to_array($leitor->ler($this->tempPath, 10));

        $this->assertSame([], $chunks);
    }

    private function criarPlanilhaMinima(int $numLinhas): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dados');

        $sheet->setCellValue('A1', 'cpf');
        $sheet->setCellValue('B1', 'nome');
        $sheet->setCellValue('C1', 'cep');
        $sheet->setCellValue('D1', 'endereco');
        $sheet->setCellValue('E1', 'numero');
        $sheet->setCellValue('F1', 'bairro');
        $sheet->setCellValue('G1', 'municipio');
        $sheet->setCellValue('H1', 'uf');
        $sheet->setCellValue('I1', 'telefone_numero');
        $sheet->setCellValue('J1', 'cod_vaga');
        $sheet->setCellValue('K1', 'centro_custo');
        $sheet->setCellValue('L1', 'tipo_admissao');
        $sheet->setCellValue('M1', 'data_admissao');
        $sheet->setCellValue('N1', 'data_aso');

        for ($i = 0; $i < $numLinhas; $i++) {
            $row = $i + 2;
            $sheet->setCellValue('A' . $row, '12345678901');
            $sheet->setCellValue('B' . $row, 'Fulano');
            $sheet->setCellValue('C' . $row, '65000000');
            $sheet->setCellValue('D' . $row, 'Rua Teste');
            $sheet->setCellValue('E' . $row, '1');
            $sheet->setCellValue('F' . $row, 'Centro');
            $sheet->setCellValue('G' . $row, 'São Luís');
            $sheet->setCellValue('H' . $row, 'MA');
            $sheet->setCellValue('I' . $row, '98987654321');
            $sheet->setCellValue('J' . $row, 1);
            $sheet->setCellValue('K' . $row, 1);
            $sheet->setCellValue('L' . $row, 'FIXO');
            $sheet->setCellValue('M' . $row, '15/03/2025');
            $sheet->setCellValue('N' . $row, '20/03/2025');
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($this->tempPath);
    }

    private function criarPlanilhaComDataExcel(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dados');

        $sheet->setCellValue('A1', 'cpf');
        $sheet->setCellValue('B1', 'nome');
        $sheet->setCellValue('C1', 'data_admissao');
        $sheet->setCellValue('D1', 'data_aso');
        $sheet->setCellValue('E1', 'cep');
        $sheet->setCellValue('F1', 'endereco');
        $sheet->setCellValue('G1', 'numero');
        $sheet->setCellValue('H1', 'bairro');
        $sheet->setCellValue('I1', 'municipio');
        $sheet->setCellValue('J1', 'uf');
        $sheet->setCellValue('K1', 'telefone_numero');
        $sheet->setCellValue('L1', 'cod_vaga');
        $sheet->setCellValue('M1', 'centro_custo');
        $sheet->setCellValue('N1', 'tipo_admissao');

        $sheet->setCellValue('A2', '12345678901');
        $sheet->setCellValue('B2', 'Fulano');
        $sheet->setCellValue('C2', 45353);
        $sheet->setCellValue('D2', 45358);
        $sheet->setCellValue('E2', '65000000');
        $sheet->setCellValue('F2', 'Rua Teste');
        $sheet->setCellValue('G2', '1');
        $sheet->setCellValue('H2', 'Centro');
        $sheet->setCellValue('I2', 'São Luís');
        $sheet->setCellValue('J2', 'MA');
        $sheet->setCellValue('K2', '98987654321');
        $sheet->setCellValue('L2', 1);
        $sheet->setCellValue('M2', 1);
        $sheet->setCellValue('N2', 'FIXO');

        $writer = new Xlsx($spreadsheet);
        $writer->save($this->tempPath);
    }

    private function criarPlanilhaComCabecalhoAsterisco(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dados');

        $sheet->setCellValue('A1', 'cpf*');
        $sheet->setCellValue('B1', 'nome*');
        $sheet->setCellValue('C1', 'data_admissao*');
        $sheet->setCellValue('D1', 'data_aso*');
        $sheet->setCellValue('E1', 'cep*');
        $sheet->setCellValue('F1', 'endereco*');
        $sheet->setCellValue('G1', 'numero*');
        $sheet->setCellValue('H1', 'bairro*');
        $sheet->setCellValue('I1', 'municipio*');
        $sheet->setCellValue('J1', 'uf*');
        $sheet->setCellValue('K1', 'telefone_numero*');
        $sheet->setCellValue('L1', 'cod_vaga*');
        $sheet->setCellValue('M1', 'centro_custo*');
        $sheet->setCellValue('N1', 'tipo_admissao*');

        $sheet->setCellValue('A2', '12345678901');
        $sheet->setCellValue('B2', 'Fulano');
        $sheet->setCellValue('C2', '15/03/2025');
        $sheet->setCellValue('D2', '20/03/2025');
        $sheet->setCellValue('E2', '65000000');
        $sheet->setCellValue('F2', 'Rua Teste');
        $sheet->setCellValue('G2', '1');
        $sheet->setCellValue('H2', 'Centro');
        $sheet->setCellValue('I2', 'São Luís');
        $sheet->setCellValue('J2', 'MA');
        $sheet->setCellValue('K2', '98987654321');
        $sheet->setCellValue('L2', 1);
        $sheet->setCellValue('M2', 1);
        $sheet->setCellValue('N2', 'FIXO');

        $writer = new Xlsx($spreadsheet);
        $writer->save($this->tempPath);
    }
}
