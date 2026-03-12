<?php

namespace Tests\Unit\Console;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class GerarPlanilhaExemploAdmissoesCommandTest extends TestCase
{
    private string $tempPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempPath = storage_path('app/test_planilha_exemplo_' . uniqid() . '.xlsx');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempPath)) {
            @unlink($this->tempPath);
        }
        parent::tearDown();
    }

    public function testCommandGeraArquivoNoPathInformado(): void
    {
        $this->artisan('mybp:gerar-planilha-exemplo-admissoes', ['--saida' => $this->tempPath])
            ->assertSuccessful();
        $this->assertFileExists($this->tempPath);
    }

    public function testArquivoPossuiAbaDados(): void
    {
        $this->artisan('mybp:gerar-planilha-exemplo-admissoes', ['--saida' => $this->tempPath])
            ->assertSuccessful();
        $spreadsheet = IOFactory::load($this->tempPath);
        $sheet = $spreadsheet->getSheetByName('Dados');
        $this->assertNotNull($sheet);
    }

    public function testPrimeiraLinhaECabecalhoComColunasEsperadas(): void
    {
        $this->artisan('mybp:gerar-planilha-exemplo-admissoes', ['--saida' => $this->tempPath])
            ->assertSuccessful();
        $spreadsheet = IOFactory::load($this->tempPath);
        $sheet = $spreadsheet->getSheetByName('Dados');
        $this->assertSame('cpf', $sheet->getCell('A1')->getValue());
        $this->assertSame('nome', $sheet->getCell('B1')->getValue());
        $this->assertSame('tipo_admissao', $sheet->getCell('AB1')->getValue());
        $this->assertSame('data_admissao', $sheet->getCell('AE1')->getValue());
    }

    public function testLinhasSeguintesPossuemDadosDeExemplo(): void
    {
        $this->artisan('mybp:gerar-planilha-exemplo-admissoes', ['--saida' => $this->tempPath])
            ->assertSuccessful();
        $spreadsheet = IOFactory::load($this->tempPath);
        $sheet = $spreadsheet->getSheetByName('Dados');
        $this->assertNotEmpty($sheet->getCell('A2')->getValue());
        $this->assertNotEmpty($sheet->getCell('B2')->getValue());
        $this->assertNotEmpty($sheet->getCell('A3')->getValue());
    }
}
