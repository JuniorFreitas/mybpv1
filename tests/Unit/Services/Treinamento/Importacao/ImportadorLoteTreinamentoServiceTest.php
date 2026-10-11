<?php

namespace Tests\Unit\Services\Treinamento\Importacao;

use App\Services\Treinamento\Importacao\ImportadorLoteTreinamentoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportadorLoteTreinamentoServiceTest extends TestCase
{
    private ImportadorLoteTreinamentoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->criarSchemaMinimo();
        $this->service = new ImportadorLoteTreinamentoService();
    }

    public function testBatchCriaTreinamentoEPivotComDataVencimentoPorPrazo(): void
    {
        $this->seedBase(empresaId: 10, feedbackId: 1, vencimentoId: 50, prazo: 365);

        $resultados = $this->service->processarChunk([
            [
                'nome' => 'Fulano',
                'cpf' => '123.456.789-09',
                'feedback_id' => 1,
                'vencimento_id' => 50,
                'data_treinamento' => '15/03/2026',
                'data_vencimento' => null,
            ],
        ], 10, 99);

        $this->assertCount(1, $resultados);
        $this->assertSame('SUCESSO', $resultados[0]['status']);
        $this->assertStringContainsString('criado', mb_strtolower($resultados[0]['mensagem']));
        $this->assertSame('15/03/2027', $resultados[0]['data_vencimento']);

        $this->assertDatabaseHas('treinamentos', [
            'feedback_id' => 1,
            'tipo' => 'Fixo',
            'cadastrou' => 99,
        ]);
        $this->assertDatabaseHas('treinamento_vencimento', [
            'vencimento_id' => 50,
            'data_treinamento' => '2026-03-15',
            'data_vencimento' => '2027-03-15',
        ]);
        $this->assertSame(1, DB::table('treinamento_vencimento_historicos')->count());
        $this->assertDatabaseHas('treinamento_vencimento_historicos', [
            'feedback_id' => 1,
            'vencimento_id' => 50,
            'removido' => 0,
            'user_id' => 99,
        ]);
    }

    public function testBatchUsaDataVencimentoDoExcelQuandoPreenchida(): void
    {
        $this->seedBase(empresaId: 10, feedbackId: 1, vencimentoId: 50, prazo: 365);

        $resultados = $this->service->processarChunk([
            [
                'nome' => 'Fulano',
                'cpf' => '123.456.789-09',
                'feedback_id' => 1,
                'vencimento_id' => 50,
                'data_treinamento' => '01/01/2026',
                'data_vencimento' => '31/12/2026',
            ],
        ], 10, 7);

        $this->assertSame('SUCESSO', $resultados[0]['status']);
        $this->assertSame('31/12/2026', $resultados[0]['data_vencimento']);
        $this->assertDatabaseHas('treinamento_vencimento', [
            'vencimento_id' => 50,
            'data_treinamento' => '2026-01-01',
            'data_vencimento' => '2026-12-31',
        ]);
    }

    public function testBatchAtualizaSemDuplicarTreinamentoEPreservaOutroVencimento(): void
    {
        $this->seedBase(empresaId: 10, feedbackId: 1, vencimentoId: 50, prazo: 30);
        DB::table('vencimentos')->insert([
            'id' => 51,
            'empresa_id' => 10,
            'label' => 'NR 35',
            'prazo_fixo' => 90,
            'ativo' => true,
        ]);

        $treinamentoId = DB::table('treinamentos')->insertGetId([
            'feedback_id' => 1,
            'cadastrou' => 1,
            'tipo' => 'Fixo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('treinamento_vencimento')->insert([
            [
                'treinamento_id' => $treinamentoId,
                'vencimento_id' => 50,
                'data_treinamento' => '2025-01-01',
                'data_vencimento' => '2025-01-31',
                'numero_fat' => 'FAT-OLD',
            ],
            [
                'treinamento_id' => $treinamentoId,
                'vencimento_id' => 51,
                'data_treinamento' => '2025-02-01',
                'data_vencimento' => '2025-05-01',
                'numero_fat' => null,
            ],
        ]);

        $resultados = $this->service->processarChunk([
            [
                'nome' => 'Fulano',
                'cpf' => '123.456.789-09',
                'feedback_id' => 1,
                'vencimento_id' => 50,
                'data_treinamento' => '10/06/2026',
                'data_vencimento' => '',
            ],
        ], 10, 5);

        $this->assertSame('SUCESSO', $resultados[0]['status']);
        $this->assertStringContainsString('atualizado', mb_strtolower($resultados[0]['mensagem']));
        $this->assertSame(1, DB::table('treinamentos')->where('feedback_id', 1)->count());
        $this->assertDatabaseHas('treinamento_vencimento', [
            'treinamento_id' => $treinamentoId,
            'vencimento_id' => 50,
            'data_treinamento' => '2026-06-10',
            'data_vencimento' => '2026-07-10',
            'numero_fat' => 'FAT-OLD',
        ]);
        $this->assertDatabaseHas('treinamento_vencimento', [
            'treinamento_id' => $treinamentoId,
            'vencimento_id' => 51,
            'data_treinamento' => '2025-02-01',
        ]);
    }

    public function testReimportIdempotenteMesmoEstadoFinal(): void
    {
        $this->seedBase(empresaId: 10, feedbackId: 1, vencimentoId: 50, prazo: 10);

        $linha = [
            'nome' => 'Fulano',
            'cpf' => '123.456.789-09',
            'feedback_id' => 1,
            'vencimento_id' => 50,
            'data_treinamento' => '01/04/2026',
            'data_vencimento' => '20/04/2026',
        ];

        $this->service->processarChunk([$linha], 10, 1);
        $this->service->processarChunk([$linha], 10, 1);

        $this->assertSame(1, DB::table('treinamentos')->count());
        $this->assertSame(1, DB::table('treinamento_vencimento')->count());
        $this->assertSame(2, DB::table('treinamento_vencimento_historicos')->count());
        $this->assertDatabaseHas('treinamento_vencimento', [
            'data_treinamento' => '2026-04-01',
            'data_vencimento' => '2026-04-20',
        ]);
    }

    public function testDuasLinhasIguaisUltimaPrevaleceNoDbEAmbasNoRelatorio(): void
    {
        $this->seedBase(empresaId: 10, feedbackId: 1, vencimentoId: 50, prazo: 5);

        $resultados = $this->service->processarChunk([
            [
                'nome' => 'Fulano',
                'cpf' => '123.456.789-09',
                'feedback_id' => 1,
                'vencimento_id' => 50,
                'data_treinamento' => '01/01/2026',
                'data_vencimento' => '10/01/2026',
            ],
            [
                'nome' => 'Fulano',
                'cpf' => '123.456.789-09',
                'feedback_id' => 1,
                'vencimento_id' => 50,
                'data_treinamento' => '05/01/2026',
                'data_vencimento' => '15/01/2026',
            ],
        ], 10, 1);

        $this->assertCount(2, $resultados);
        $this->assertSame('SUCESSO', $resultados[0]['status']);
        $this->assertSame('SUCESSO', $resultados[1]['status']);
        $this->assertSame(1, DB::table('treinamento_vencimento')->count());
        $this->assertDatabaseHas('treinamento_vencimento', [
            'data_treinamento' => '2026-01-05',
            'data_vencimento' => '2026-01-15',
        ]);
    }

    private function criarSchemaMinimo(): void
    {
        Schema::dropIfExists('treinamento_vencimento_historicos');
        Schema::dropIfExists('treinamento_vencimento');
        Schema::dropIfExists('treinamentos');
        Schema::dropIfExists('vencimentos');
        Schema::dropIfExists('feedback_curriculos');
        Schema::dropIfExists('curriculos');

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
            $table->softDeletes();
        });
        Schema::create('vencimentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('label')->nullable();
            $table->integer('prazo_fixo')->nullable();
            $table->boolean('ativo')->default(true);
        });
        Schema::create('treinamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->unsignedBigInteger('cadastrou')->nullable();
            $table->string('tipo')->nullable();
            $table->timestamps();
        });
        Schema::create('treinamento_vencimento', function (Blueprint $table) {
            $table->unsignedBigInteger('treinamento_id');
            $table->unsignedBigInteger('vencimento_id');
            $table->date('data_treinamento')->nullable();
            $table->date('data_vencimento')->nullable();
            $table->string('numero_fat')->nullable();
            $table->unsignedBigInteger('arquivo_id')->nullable();
        });
        Schema::create('treinamento_vencimento_historicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('treinamento_id');
            $table->unsignedBigInteger('vencimento_id')->nullable();
            $table->boolean('removido')->default(false);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('treinamentos_vencimentos')->nullable();
            $table->timestamps();
        });
    }

    private function seedBase(int $empresaId, int $feedbackId, int $vencimentoId, int $prazo): void
    {
        DB::table('curriculos')->insert([
            'id' => $feedbackId,
            'nome' => 'Fulano Silva',
            'cpf' => '123.456.789-09',
        ]);
        DB::table('feedback_curriculos')->insert([
            'id' => $feedbackId,
            'curriculo_id' => $feedbackId,
            'empresa_id' => $empresaId,
        ]);
        DB::table('vencimentos')->insert([
            'id' => $vencimentoId,
            'empresa_id' => $empresaId,
            'label' => 'NR 10',
            'prazo_fixo' => $prazo,
            'ativo' => true,
        ]);
    }
}
