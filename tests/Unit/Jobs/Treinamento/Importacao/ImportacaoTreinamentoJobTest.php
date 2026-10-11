<?php

namespace Tests\Unit\Jobs\Treinamento\Importacao;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Jobs\Treinamento\Importacao\ImportacaoTreinamentoJob;
use App\Models\Exportacao;
use App\Models\Treinamento\ImportacaoTreinamento;
use App\Services\Treinamento\Importacao\DivisorRelatorioImportacaoTreinamento;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

class ImportacaoTreinamentoJobTest extends TestCase
{
    public function testJobPodeSerEnfileirado(): void
    {
        Queue::fake();
        ImportacaoTreinamentoJob::dispatch('importacao_treinamentos/arquivo.xlsx', 1, null);
        Queue::assertPushed(ImportacaoTreinamentoJob::class);
    }

    public function testHandleLancaExcecaoQuandoArquivoNaoExiste(): void
    {
        $this->criarSchemaHistorico();
        ImportacaoTreinamento::create([
            'empresa_id' => 1,
            'user_id' => null,
            'uuid' => 'uuid-falha-arquivo',
            'status' => ImportacaoTreinamento::STATUS_PROCESSANDO,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Arquivo não encontrado');

        $job = new ImportacaoTreinamentoJob(
            'caminho/inexistente_xyz_999.xlsx',
            1,
            null,
            'uuid-falha-arquivo'
        );
        $job->handle(
            app(\App\Services\Treinamento\Importacao\LeitorPlanilhaImportacaoTreinamento::class),
            app(DivisorRelatorioImportacaoTreinamento::class)
        );
    }

    public function testPublicarRelatorioCriaHistoricoExportacaoEDisparaNotificacao(): void
    {
        Event::fake([NotificacaoEvent::class]);
        Storage::fake('disco-exportacao');
        $this->criarSchemaDownloads();
        $this->criarSchemaHistorico();

        ImportacaoTreinamento::create([
            'empresa_id' => 77,
            'user_id' => 42,
            'uuid' => 'uuid-teste-relatorio',
            'status' => ImportacaoTreinamento::STATUS_PROCESSANDO,
        ]);

        $csvPath = storage_path('app/importacao_treinamentos/relatorios/77/teste_resultado.csv');
        if (!is_dir(dirname($csvPath))) {
            mkdir(dirname($csvPath), 0755, true);
        }
        file_put_contents($csvPath, "\xEF\xBB\xBFNome;CPF;Treinamento;Data Treinamento;Data Vencimento;Status;Mensagem;ID Treinamento\n"
            . "Fulano;123;NR-10;01/01/2026;01/01/2027;SUCESSO;ok;1\n"
            . "Ciclano;456;NR-35;01/01/2026;01/01/2027;ERRO;falhou;\n");

        $job = new ImportacaoTreinamentoJob(
            'importacao_treinamentos/arquivo.xlsx',
            77,
            42,
            'uuid-teste-relatorio'
        );

        $method = new ReflectionMethod(ImportacaoTreinamentoJob::class, 'publicarRelatorioENotificar');
        $method->setAccessible(true);
        $method->invoke($job, $csvPath, app(DivisorRelatorioImportacaoTreinamento::class));

        $historico = ImportacaoTreinamento::query()->where('uuid', 'uuid-teste-relatorio')->first();
        $this->assertNotNull($historico);
        $this->assertSame(ImportacaoTreinamento::STATUS_CONCLUIDA, $historico->status);
        $this->assertSame(2, $historico->total);
        $this->assertSame(1, $historico->sucesso);
        $this->assertSame(1, $historico->erros);
        $this->assertNotEmpty($historico->arquivo_sucesso);
        $this->assertNotEmpty($historico->arquivo_erro);
        $this->assertTrue(Storage::disk('disco-exportacao')->exists($historico->arquivo_sucesso));
        $this->assertTrue(Storage::disk('disco-exportacao')->exists($historico->arquivo_erro));

        $exportacao = Exportacao::query()->where('user_id', 42)->first();
        $this->assertNotNull($exportacao);
        $this->assertSame('Importação de Treinamentos', $exportacao->local);

        Event::assertDispatched(NotificacaoEvent::class, function (NotificacaoEvent $event) {
            return $event->evento === NotificacaoEvent::IMPORTACAO_TREINAMENTOS_CONCLUIDA
                && ($event->dados['user_id'] ?? null) === 42
                && ($event->dados['total_sucesso'] ?? null) === 1
                && ($event->dados['total_erros'] ?? null) === 1;
        });
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

    private function criarSchemaDownloads(): void
    {
        Schema::dropIfExists('exportacaos');
        Schema::dropIfExists('activity_log');
        Schema::create('exportacaos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('arquivo', 255);
            $table->string('local');
            $table->boolean('removido')->default(false);
            $table->timestamps();
        });
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description')->nullable();
            $table->nullableMorphs('subject');
            $table->nullableMorphs('causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->string('event')->nullable();
            $table->string('descricao')->nullable();
            $table->timestamps();
        });
    }
}
