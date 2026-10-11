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
        $this->assertNull($registros[0]['data_vencimento']);
    }

    public function testLerAceitaDataVencimentoOpcional(): void
    {
        $this->criarPlanilha([
            [
                '1|111.111.111-11|Fulano|Cargo',
                '10|GERAL|NR10',
                '15/03/2026',
                '15/03/2027',
            ],
        ], comDataVencimento: true);

        $leitor = new LeitorPlanilhaImportacaoTreinamento();
        $registros = $leitor->ler($this->tempPath, 1);

        $this->assertCount(1, $registros);
        $this->assertSame('15/03/2026', $registros[0]['data_treinamento']);
        $this->assertSame('15/03/2027', $registros[0]['data_vencimento']);
    }

    public function testLerDataVencimentoVaziaViraNull(): void
    {
        $this->criarPlanilha([
            [
                '1|111.111.111-11|Fulano|Cargo',
                '10|GERAL|NR10',
                '15/03/2026',
                '',
            ],
        ], comDataVencimento: true);

        $leitor = new LeitorPlanilhaImportacaoTreinamento();
        $registros = $leitor->ler($this->tempPath, 1);

        $this->assertNull($registros[0]['data_vencimento']);
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

    public function testLerAceitaDataExcelComFormatoDataNativa20012020(): void
    {
        // Caso real: célula de data do Excel (serial), exibida como 20/01/2020
        $serial = \PhpOffice\PhpSpreadsheet\Shared\Date::stringToExcel('2020-01-20');
        $this->assertNotFalse($serial);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dados');
        $sheet->setCellValue('A1', 'colaborador');
        $sheet->setCellValue('B1', 'treinamento');
        $sheet->setCellValue('C1', 'data_treinamento');
        $sheet->setCellValue('D1', 'data_vencimento');
        $sheet->setCellValue('A2', '24217|029.573.402-75|ABIMAEL|CALDEIREIRO');
        $sheet->setCellValue('B2', '403|4600010536|Ambientação');
        $sheet->setCellValue('C2', $serial);
        $sheet->getStyle('C2')->getNumberFormat()->setFormatCode('DD/MM/YYYY');
        $sheet->setCellValue('D2', null);

        (new Xlsx($spreadsheet))->save($this->tempPath);
        $spreadsheet->disconnectWorksheets();

        $leitor = new LeitorPlanilhaImportacaoTreinamento();
        $registros = $leitor->ler($this->tempPath, 1);

        $this->assertCount(1, $registros);
        $this->assertSame('20/01/2020', $registros[0]['data_treinamento']);
        $this->assertNull($registros[0]['data_vencimento']);
    }

    public function testLerAceitaDataComHoraOuSeparadoresAlternativos(): void
    {
        $this->criarPlanilha([
            [
                '1|111.111.111-11|Fulano|Cargo',
                '10|GERAL|NR10',
                '20/01/2020 00:00:00',
                '09.05.2024',
            ],
        ], comDataVencimento: true);

        $leitor = new LeitorPlanilhaImportacaoTreinamento();
        $registros = $leitor->ler($this->tempPath, 1);

        $this->assertSame('20/01/2020', $registros[0]['data_treinamento']);
        $this->assertSame('09/05/2024', $registros[0]['data_vencimento']);
    }

    public function testLerDataInvalidaLancaExcecaoComFormatoEsperado(): void
    {
        $this->criarPlanilha([
            [
                '1|111.111.111-11|Fulano|Cargo',
                '10|GERAL|NR10',
                '32/13/2026',
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
     * @param list<array{0:string,1:string,2:mixed,3?:mixed}> $linhas
     */
    private function criarPlanilha(array $linhas, bool $comDataVencimento = false): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dados');
        $sheet->setCellValue('A1', 'colaborador');
        $sheet->setCellValue('B1', 'treinamento');
        $sheet->setCellValue('C1', 'data_treinamento');
        if ($comDataVencimento) {
            $sheet->setCellValue('D1', 'data_vencimento');
        }

        $row = 2;
        foreach ($linhas as $linha) {
            $sheet->setCellValue('A' . $row, $linha[0]);
            $sheet->setCellValue('B' . $row, $linha[1]);
            $sheet->setCellValue('C' . $row, $linha[2]);
            if ($comDataVencimento && array_key_exists(3, $linha)) {
                $sheet->setCellValue('D' . $row, $linha[3]);
            }
            $row++;
        }

        (new Xlsx($spreadsheet))->save($this->tempPath);
        $spreadsheet->disconnectWorksheets();
    }
}
