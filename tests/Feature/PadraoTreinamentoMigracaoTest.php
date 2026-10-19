<?php

namespace Tests\Feature;

use App\Models\PadraoTreinamento;
use App\Models\PadraoTreinamentoLotacao;
use App\Services\Treinamento\MigrarSegmentosParaPadroesTreinamento;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PadraoTreinamentoMigracaoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->criarSchemaMinimo();
    }

    public function testMigraSegmentoParaCarteiraEtiquetaEPadraoComLotacaoMatriz(): void
    {
        DB::table('clientes')->insert([
            'id' => 500,
            'tipo_cliente' => 'Cliente',
            'tipo' => 'J',
            'area_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $segmentoId = DB::table('segmentos_treinamento')->insertGetId([
            'nome' => 'ALUMAR',
            'slug' => 'alumar',
            'ativo' => 1,
            'config_carteira' => json_encode([
                'cabecalho_img' => 'images/carteira/cab.webp',
                'verso_img' => 'images/carteira/verso.webp',
                'exibir_etiqueta_bloqueio' => true,
                'ramal_emergencia' => '1199',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('cliente_segmento_treinamento')->insert([
            'cliente_id' => 500,
            'segmento_treinamento_id' => $segmentoId,
        ]);

        $vencimentoId = DB::table('vencimentos')->insertGetId([
            'label' => 'NR-10',
            'ativo' => 1,
            'empresa_id' => 500,
            'segmento_treinamento_id' => $segmentoId,
        ]);

        $stats = (new MigrarSegmentosParaPadroesTreinamento())->executar();

        $this->assertGreaterThanOrEqual(1, $stats['carteiras']);
        $this->assertGreaterThanOrEqual(1, $stats['etiquetas']);
        $this->assertGreaterThanOrEqual(1, $stats['padroes']);
        $this->assertGreaterThanOrEqual(1, $stats['lotacoes']);

        $this->assertDatabaseHas('carteiras_treinamento', [
            'slug' => 'alumar',
            'empresa_id' => null,
            'cabecalho_img' => 'images/carteira/cab.webp',
        ]);

        $padrao = PadraoTreinamento::query()->where('empresa_id', 500)->where('slug', 'alumar')->first();
        $this->assertNotNull($padrao);
        $this->assertTrue($padrao->tem_carteira);
        $this->assertTrue($padrao->tem_etiqueta_bloqueio);

        $this->assertTrue(
            PadraoTreinamentoLotacao::query()
                ->where('padrao_treinamento_id', $padrao->id)
                ->where('lotacao_key', 'matriz')
                ->exists()
        );

        $this->assertDatabaseHas('padrao_treinamento_vencimento', [
            'padrao_treinamento_id' => $padrao->id,
            'vencimento_id' => $vencimentoId,
        ]);
    }

    public function testBackfillAdmissaoSemSegmentoUsaPadraoDefaultAlumar(): void
    {
        DB::table('clientes')->insert([
            'id' => 502,
            'tipo_cliente' => 'Cliente',
            'tipo' => 'J',
            'area_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $segmentoId = DB::table('segmentos_treinamento')->insertGetId([
            'nome' => 'ALUMAR',
            'slug' => 'alumar',
            'ativo' => 1,
            'config_carteira' => json_encode(['exibir_etiqueta_bloqueio' => true]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('cliente_segmento_treinamento')->insert([
            'cliente_id' => 502,
            'segmento_treinamento_id' => $segmentoId,
        ]);

        $feedbackId = DB::table('feedback_curriculos')->insertGetId([
            'empresa_id' => 502,
        ]);

        $admissaoId = DB::table('admissoes')->insertGetId([
            'feedback_id' => $feedbackId,
            'segmento_treinamento_id' => null,
            'padrao_treinamento_id' => null,
        ]);

        (new MigrarSegmentosParaPadroesTreinamento())->executar();

        $padraoId = DB::table('padroes_treinamento')
            ->where('empresa_id', 502)
            ->where('slug', 'alumar')
            ->value('id');

        $this->assertNotNull($padraoId);
        $this->assertDatabaseHas('admissoes', [
            'id' => $admissaoId,
            'padrao_treinamento_id' => $padraoId,
        ]);
    }

    public function testPadraoParaLotacaoFiltraMatrizEFilial(): void
    {
        DB::table('clientes')->insert([
            'id' => 501,
            'tipo_cliente' => 'Cliente',
            'tipo' => 'J',
            'area_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $filialId = DB::table('cliente_filials')->insertGetId([
            'empresa_id' => 501,
            'dados' => json_encode(['nome_fantasia' => 'Filial A']),
            'ativo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $padraoMatrizId = DB::table('padroes_treinamento')->insertGetId([
            'empresa_id' => 501,
            'nome' => 'Só Matriz',
            'slug' => 'so-matriz',
            'ativo' => 1,
            'tem_carteira' => 0,
            'tem_etiqueta_bloqueio' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('padrao_treinamento_lotacao')->insert([
            'padrao_treinamento_id' => $padraoMatrizId,
            'is_matriz' => 1,
            'cliente_filial_id' => null,
            'lotacao_key' => 'matriz',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $padraoFilialId = DB::table('padroes_treinamento')->insertGetId([
            'empresa_id' => 501,
            'nome' => 'Só Filial',
            'slug' => 'so-filial',
            'ativo' => 1,
            'tem_carteira' => 0,
            'tem_etiqueta_bloqueio' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('padrao_treinamento_lotacao')->insert([
            'padrao_treinamento_id' => $padraoFilialId,
            'is_matriz' => 0,
            'cliente_filial_id' => $filialId,
            'lotacao_key' => 'f:' . $filialId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $idsMatriz = PadraoTreinamento::query()->where('empresa_id', 501)->paraLotacao(true)->pluck('id')->all();
        $idsFilial = PadraoTreinamento::query()->where('empresa_id', 501)->paraLotacao(false, $filialId)->pluck('id')->all();

        $this->assertContains($padraoMatrizId, $idsMatriz);
        $this->assertNotContains($padraoFilialId, $idsMatriz);
        $this->assertContains($padraoFilialId, $idsFilial);
        $this->assertNotContains($padraoMatrizId, $idsFilial);
    }

    private function criarSchemaMinimo(): void
    {
        Schema::dropIfExists('padrao_treinamento_vencimento');
        Schema::dropIfExists('padrao_treinamento_lotacao');
        Schema::dropIfExists('admissoes');
        Schema::dropIfExists('feedback_curriculos');
        Schema::dropIfExists('carteira_assinaturas');
        Schema::dropIfExists('padroes_treinamento');
        Schema::dropIfExists('etiquetas_bloqueio');
        Schema::dropIfExists('carteiras_treinamento');
        Schema::dropIfExists('cliente_segmento_treinamento');
        Schema::dropIfExists('vencimentos');
        Schema::dropIfExists('segmentos_treinamento');
        Schema::dropIfExists('cliente_filials');
        Schema::dropIfExists('clientes');

        Schema::create('clientes', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('tipo_cliente')->default('Cliente');
            $table->string('tipo')->default('J');
            $table->unsignedBigInteger('area_id')->default(1);
            $table->timestamps();
        });

        Schema::create('cliente_filials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->json('dados')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('segmentos_treinamento', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug', 50);
            $table->boolean('ativo')->default(true);
            $table->json('config_carteira')->nullable();
            $table->timestamps();
        });

        Schema::create('cliente_segmento_treinamento', function (Blueprint $table) {
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('segmento_treinamento_id');
            $table->primary(['cliente_id', 'segmento_treinamento_id']);
        });

        Schema::create('vencimentos', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->boolean('ativo')->default(true);
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('segmento_treinamento_id')->nullable();
        });

        Schema::create('carteiras_treinamento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome');
            $table->string('slug', 50);
            $table->boolean('ativo')->default(true);
            $table->string('cabecalho_img')->nullable();
            $table->string('verso_img')->nullable();
            $table->timestamps();
        });

        Schema::create('etiquetas_bloqueio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome');
            $table->string('slug', 50);
            $table->boolean('ativo')->default(true);
            $table->string('ramal_emergencia')->nullable();
            $table->string('bloqueio_texto_nao_use')->nullable();
            $table->string('bloqueio_texto_demissao')->nullable();
            $table->string('bloqueio_texto_cuidado')->nullable();
            $table->string('bloqueio_texto_homens_trabalhando')->nullable();
            $table->timestamps();
        });

        Schema::create('padroes_treinamento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome');
            $table->string('slug', 50);
            $table->boolean('ativo')->default(true);
            $table->boolean('tem_carteira')->default(false);
            $table->unsignedBigInteger('carteira_treinamento_id')->nullable();
            $table->boolean('tem_etiqueta_bloqueio')->default(false);
            $table->unsignedBigInteger('etiqueta_bloqueio_id')->nullable();
            $table->timestamps();
        });

        Schema::create('padrao_treinamento_lotacao', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('padrao_treinamento_id');
            $table->boolean('is_matriz')->default(false);
            $table->unsignedBigInteger('cliente_filial_id')->nullable();
            $table->string('lotacao_key', 32);
            $table->timestamps();
        });

        Schema::create('padrao_treinamento_vencimento', function (Blueprint $table) {
            $table->unsignedBigInteger('padrao_treinamento_id');
            $table->unsignedBigInteger('vencimento_id');
            $table->primary(['padrao_treinamento_id', 'vencimento_id']);
        });

        Schema::create('feedback_curriculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
        });

        Schema::create('admissoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable();
            $table->unsignedBigInteger('segmento_treinamento_id')->nullable();
            $table->unsignedBigInteger('padrao_treinamento_id')->nullable();
        });

        Schema::create('carteira_assinaturas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('segmento_treinamento_id')->nullable();
            $table->unsignedBigInteger('padrao_treinamento_id')->nullable();
        });
    }
}
