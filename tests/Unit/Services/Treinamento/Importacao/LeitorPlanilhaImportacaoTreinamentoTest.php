<?php

namespace Tests\Unit\Services\Treinamento\Importacao;

use App\Services\Treinamento\Importacao\LeitorPlanilhaImportacaoTreinamento;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Tests\TestCase;

class LeitorPlanilhaImportacaoTreinamentoTest extends TestCase
{
    private string $tempPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempPath = storage_path('app/test_import_treinamento_' . uniqid() . '.xlsx');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempPath)) {
            @unlink($this->tempPath);
        }
        parent::tearDown();
    }

    public function testLerConverteLinhaPipeParaPayloadDoComando(): void
    {
        $this->criarPlanilha([
            [
                '28613|329.854.758-13|ABDIAS SOUSA|MECANICO',
                '527|ALUMAR|CTPV',
                '23/01/2026',
            ],
        ]);

        $leitor = new LeitorPlanilhaImportacaoTreinamento();
        $registros = $leitor->ler($this->tempPath, 103967);

        $this->assertCount(1, $registros);
        $this->assertSame('ABDIAS SOUSA', $registros[0]['nome']);
        $this->assertSame('329.854.758-13', $registros[0]['cpf']);
        $this->assertSame('ALUMAR', $registros[0]['contrato']);
        $this->assertSame('28613', $registros[0]['matricula']);
        $this->assertSame('28613', $registros[0]['feedback_id']);
        $this->assertSame('23/01/2026', $registros[0]['data_treinamento']);
        $this->assertSame('103967', $registros[0]['empresa_id']);
        $this->assertSame('527', $registros[0]['vencimento_id']);
        $this->assertSame('Fixo', $registros[0]['tipo']);
    }

    public function testLerConverteDataSerialExcel(): void
    {
        $this->criarPlanilha([
            [
                '1|111.111.111-11|Fulano|Cargo',
                '10|GERAL|NR10',
                46045, // serial excel
            ],
        ]);

        $leitor = new LeitorPlanilhaImportacaoTreinamento();
        $registros = $leitor->ler($this->tempPath, 1);

        $this->assertCount(1, $registros);
        $this->assertMatchesRegularExpression('/^\d{2}\/\d{2}\/\d{4}$/', $registros[0]['data_treinamento']);
    }

    public function testLerDataInvalidaLancaExcecaoComFormatoEsperado(): void
    {
        $this->criarPlanilha([
            [
                '1|111.111.111-11|Fulano|Cargo',
                '10|GERAL|NR10',
                '15-03-2026',
            ],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('dd/mm/aaaa');

        $leitor = new LeitorPlanilhaImportacaoTreinamento();
        $leitor->ler($this->tempPath, 1);
    }

    public function testLerArquivoInexistenteLancaExcecao(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('não encontrado');

        $leitor = new LeitorPlanilhaImportacaoTreinamento();
        $leitor->ler('arquivo_inexistente_xyz.xlsx', 1);
    }

    public function testLerPlanilhaVaziaRetornaListaVazia(): void
    {
        $this->criarPlanilha([]);
        $leitor = new LeitorPlanilhaImportacaoTreinamento();
        $this->assertSame([], $leitor->ler($this->tempPath, 1));
    }

    /**
     * @param list<array{0:string,1:string,2:mixed}> $linhas
     */
    private function criarPlanilha(array $linhas): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dados');
        $sheet->setCellValue('A1', 'colaborador');
        $sheet->setCellValue('B1', 'treinamento');
        $sheet->setCellValue('C1', 'data_treinamento');

        $row = 2;
        foreach ($linhas as $linha) {
            $sheet->setCellValue('A' . $row, $linha[0]);
            $sheet->setCellValue('B' . $row, $linha[1]);
            $sheet->setCellValue('C' . $row, $linha[2]);
            $row++;
        }

        (new Xlsx($spreadsheet))->save($this->tempPath);
        $spreadsheet->disconnectWorksheets();
    }
}
