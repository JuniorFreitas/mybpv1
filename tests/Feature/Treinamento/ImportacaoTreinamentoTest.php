<?php

namespace Tests\Feature\Treinamento;

use App\Jobs\Treinamento\Importacao\ImportacaoTreinamentoJob;
use App\Models\Arquivo;
use App\Models\Treinamento\ImportacaoTreinamento;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ImportacaoTreinamentoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function testDownloadModeloRetornaXlsxParaEmpresaDoUsuario(): void
    {
        $this->criarSchemaListas();
        $user = $this->criarUsuario(10);
        $this->actingAs($user);

        $response = $this->get(route('g.treinamentos.import.modelo'));

        $response->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml',
            (string) $response->headers->get('content-type')
        );
    }

    public function testUploadEnfileiraJob(): void
    {
        Queue::fake();
        Storage::fake('local');
        Storage::fake('disco-exportacao');
        $this->criarSchemaHistorico();

        $user = $this->criarUsuario(20);
        $this->actingAs($user);

        $xlsx = $this->criarArquivoUpload();
        $response = $this->post(route('g.treinamentos.import.upload'), [
            'planilha' => $xlsx,
            'empresa_id' => 20,
        ]);

        $response->assertStatus(202);
        Queue::assertPushed(ImportacaoTreinamentoJob::class, function (ImportacaoTreinamentoJob $job) {
            return $job->empresaId === 20;
        });
        $this->assertDatabaseHas('importacao_treinamentos', [
            'empresa_id' => 20,
            'status' => ImportacaoTreinamento::STATUS_PROCESSANDO,
        ]);
    }

    public function testHistoricoListaImportacoesDaEmpresa(): void
    {
        $this->criarSchemaHistorico();
        $user = $this->criarUsuario(40);
        $this->actingAs($user);

        ImportacaoTreinamento::create([
            'empresa_id' => 40,
            'user_id' => 40,
            'uuid' => 'uuid-hist-1',
            'status' => ImportacaoTreinamento::STATUS_CONCLUIDA,
            'total' => 5,
            'sucesso' => 4,
            'erros' => 1,
            'arquivo_sucesso' => 'importacao_treinamentos/relatorios/40/uuid-hist-1_sucesso.csv',
            'arquivo_erro' => 'importacao_treinamentos/relatorios/40/uuid-hist-1_erro.csv',
        ]);
        ImportacaoTreinamento::create([
            'empresa_id' => 99,
            'user_id' => 99,
            'uuid' => 'uuid-outra-empresa',
            'status' => ImportacaoTreinamento::STATUS_CONCLUIDA,
            'total' => 1,
            'sucesso' => 1,
            'erros' => 0,
        ]);

        $response = $this->getJson(route('g.treinamentos.import.historico'));
        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame(4, $data[0]['sucesso']);
        $this->assertTrue($data[0]['tem_csv_sucesso']);
        $this->assertTrue($data[0]['tem_csv_erro']);
    }

    public function testDownloadCsvSucessoDaEmpresa(): void
    {
        $this->criarSchemaHistorico();
        Storage::fake('disco-exportacao');
        $user = $this->criarUsuario(50);
        $this->actingAs($user);

        $caminho = 'importacao_treinamentos/relatorios/50/uuid-dl_sucesso.csv';
        Storage::disk(Arquivo::DISCO_EXPORTACAO)->put($caminho, "Nome;Status\nA;SUCESSO\n");

        $importacao = ImportacaoTreinamento::create([
            'empresa_id' => 50,
            'user_id' => 50,
            'uuid' => 'uuid-dl',
            'status' => ImportacaoTreinamento::STATUS_CONCLUIDA,
            'total' => 1,
            'sucesso' => 1,
            'erros' => 0,
            'arquivo_sucesso' => $caminho,
        ]);

        $response = $this->get(route('g.treinamentos.import.historico.csv', [
            'importacao' => $importacao->id,
            'tipo' => 'sucesso',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('SUCESSO', $response->streamedContent());
    }

    public function testUploadRejeitaEmpresaAlheia(): void
    {
        $user = $this->criarUsuario(30);
        $this->actingAs($user);

        $xlsx = $this->criarArquivoUpload();
        $response = $this->postJson(route('g.treinamentos.import.upload'), [
            'planilha' => $xlsx,
            'empresa_id' => 999,
        ]);

        $response->assertStatus(422);
    }

    private function criarSchemaHistorico(): void
    {
        Schema::dropIfExists('importacao_treinamentos');
        Schema::create('importacao_treinamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->uuid('uuid')->unique();
            $table->string('status', 20)->default('processando');
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('sucesso')->default(0);
            $table->unsignedInteger('erros')->default(0);
            $table->string('arquivo_sucesso')->nullable();
            $table->string('arquivo_erro')->nullable();
            $table->string('mensagem_erro', 500)->nullable();
            $table->timestamps();
        });
    }

    private function criarSchemaListas(): void
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

    private function criarUsuario(int $empresaId): User
    {
        return new User([
            'id' => $empresaId,
            'nome' => 'Tester',
            'login' => 'tester' . $empresaId . '@example.com',
            'tipo' => 'ADMINISTRADOR',
            'ativo' => true,
            'temp' => false,
            'empresa_id' => $empresaId,
        ]);
    }

    private function criarArquivoUpload(): UploadedFile
    {
        $path = storage_path('app/test_upload_treinamento_' . uniqid() . '.xlsx');
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dados');
        $sheet->setCellValue('A1', 'colaborador');
        $sheet->setCellValue('B1', 'treinamento');
        $sheet->setCellValue('C1', 'data_treinamento');
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return new UploadedFile($path, 'modelo.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
