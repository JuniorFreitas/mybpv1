<?php

namespace Tests\Unit\Services\Treinamento\Importacao;

use App\Services\Treinamento\Importacao\GeradorModeloImportacaoTreinamento;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class GeradorModeloImportacaoTreinamentoTest extends TestCase
{
    private string $tempPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempPath = storage_path('app/test_modelo_treinamento_' . uniqid() . '.xlsx');
        $this->criarSchemaMinimo();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempPath)) {
            @unlink($this->tempPath);
        }
        parent::tearDown();
    }

    public function testGerarCriaAbasDadosEListasComCabecalhos(): void
    {
        $gerador = new GeradorModeloImportacaoTreinamento();
        $modelo = $gerador->gerar(10);

        file_put_contents($this->tempPath, $modelo['binario']);
        $this->assertStringContainsString('modelo_importacao_treinamentos_10', $modelo['nome_arquivo']);

        $spreadsheet = IOFactory::load($this->tempPath);
        $dados = $spreadsheet->getSheetByName('Dados');
        $listas = $spreadsheet->getSheetByName('Listas');
        $instrucoes = $spreadsheet->getSheetByName('Instrucoes');

        $this->assertNotNull($dados);
        $this->assertNotNull($listas);
        $this->assertNotNull($instrucoes);
        $this->assertSame('colaborador', $dados->getCell('A1')->getValue());
        $this->assertSame('treinamento', $dados->getCell('B1')->getValue());
        $this->assertSame('data_treinamento', $dados->getCell('C1')->getValue());
        $this->assertSame('data_vencimento', $dados->getCell('D1')->getValue());
        $textoInstrucoes = '';
        foreach ($instrucoes->toArray() as $linha) {
            $textoInstrucoes .= implode(' ', $linha ?? []);
        }
        $this->assertStringContainsString('dd/mm/aaaa', $textoInstrucoes);
        $this->assertStringContainsString('data_vencimento', $textoInstrucoes);
    }

    public function testGerarIncluiListasDoTenant(): void
    {
        $this->inserirColaboradorETreinamento(10);

        $gerador = new GeradorModeloImportacaoTreinamento();
        $modelo = $gerador->gerar(10);
        file_put_contents($this->tempPath, $modelo['binario']);

        $spreadsheet = IOFactory::load($this->tempPath);
        $listas = $spreadsheet->getSheetByName('Listas');

        $this->assertSame('99|ALUMAR|NR 10', $listas->getCell('A1')->getValue());
        // colaborador: feedback_id|cpf|nome|cargo
        $this->assertSame('1|123.456.789-09|Fulano Silva|Soldador', $listas->getCell('B1')->getValue());
    }

    private function criarSchemaMinimo(): void
    {
        Schema::dropIfExists('demissaos');
        Schema::dropIfExists('resultado_integrados');
        Schema::dropIfExists('admissoes');
        Schema::dropIfExists('vagas');
        Schema::dropIfExists('vagas_abertas');
        Schema::dropIfExists('feedback_curriculos');
        Schema::dropIfExists('curriculos');
        Schema::dropIfExists('vencimentos');

        Schema::create('curriculos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('cpf')->nullable();
            $table->softDeletes();
        });

        Schema::create('feedback_curriculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('vagas_abertas_id')->nullable();
            $table->softDeletes();
        });

        Schema::create('vagas_abertas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vaga_id')->nullable();
        });

        Schema::create('vagas', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
        });

        Schema::create('admissoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->string('matricula')->nullable();
            $table->string('cargo')->nullable();
            $table->string('contrato')->nullable();
            $table->string('status')->nullable();
            $table->softDeletes();
        });

        Schema::create('resultado_integrados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->boolean('encaminhado_treinamento')->default(false);
        });

        Schema::create('demissaos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
        });

        Schema::create('vencimentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('label')->nullable();
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->nullable();
        });
    }

    private function inserirColaboradorETreinamento(int $empresaId): void
    {
        DB::table('curriculos')->insert([
            'id' => 1,
            'nome' => 'Fulano Silva',
            'cpf' => '123.456.789-09',
        ]);
        DB::table('feedback_curriculos')->insert([
            'id' => 1,
            'curriculo_id' => 1,
            'empresa_id' => $empresaId,
            'vagas_abertas_id' => null,
        ]);
        DB::table('admissoes')->insert([
            'feedback_id' => 1,
            'matricula' => '100',
            'cargo' => 'Soldador',
            'contrato' => 'ALUMAR',
        ]);
        DB::table('resultado_integrados')->insert([
            'feedback_id' => 1,
            'encaminhado_treinamento' => true,
        ]);
        DB::table('vencimentos')->insert([
            'id' => 99,
            'empresa_id' => $empresaId,
            'label' => 'NR 10',
            'ativo' => true,
            'ordem' => 1,
        ]);
    }
}
