<?php

namespace Tests\Unit\Domain\Exames;

use App\Domain\Exames\Services\ExameFormularioBuilderService;
use App\Domain\Exames\Services\ExameFormularioResolver;
use App\Domain\Exames\Services\ExameResultadoCompatService;
use App\Domain\Exames\Services\ExameTipoAdminService;
use App\Models\ExameTipo;
use App\Models\Formulario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExameAdminServicesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        $this->criarSchema();
    }

    public function test_compat_detecta_formato_legado(): void
    {
        $compat = new ExameResultadoCompatService();

        $this->assertTrue($compat->isFormatoLegado(['result' => 'Apto', 'aprovado' => 'Sim']));
        $this->assertFalse($compat->isFormatoLegado([
            'alternativa_id_10' => ['valor' => 'Apto', 'alternativa_id' => 10, 'tipo' => 'select'],
        ]));
    }

    public function test_compat_espelha_chaves_canonicas_na_gravacao(): void
    {
        Schema::table('alternativa_formularios', function (Blueprint $table) {
            // already created in schema
        });

        \DB::table('alternativa_formularios')->insert([
            'id' => 50,
            'empresa_id' => 1,
            'nome' => 'Resultado',
            'tipo' => 'select',
            'ativo' => 1,
            'chave_canonica' => 'result',
        ]);

        $compat = new ExameResultadoCompatService();
        $out = $compat->normalizarParaGravacao([
            'alternativa_id_50' => ['valor' => 'Apto', 'alternativa_id' => 50, 'tipo' => 'select'],
        ]);

        $this->assertSame('Apto', $out['result']);
        $this->assertArrayHasKey('alternativa_id_50', $out);
        $this->assertArrayHasKey('aprovado', $out);
    }

    public function test_resolver_usa_fallback_titulo_exames(): void
    {
        Formulario::withoutGlobalScopes()->create([
            'empresa_id' => 7,
            'titulo' => 'Exames',
            'descricao' => null,
        ]);

        $resolver = new ExameFormularioResolver();
        $form = $resolver->resolverEncaminhamento(null, 7);

        $this->assertNotNull($form);
        $this->assertSame('Exames', $form->titulo);
    }

    public function test_resolver_prioriza_formulario_vinculado_ao_tipo(): void
    {
        $formPadrao = Formulario::withoutGlobalScopes()->create([
            'empresa_id' => 7,
            'titulo' => 'Exames',
            'descricao' => null,
        ]);
        $formCustom = Formulario::withoutGlobalScopes()->create([
            'empresa_id' => 7,
            'titulo' => 'Exames Admissional',
            'descricao' => null,
        ]);

        $tipo = ExameTipo::create([
            'empresa_id' => 7,
            'label' => 'Admissional Custom',
            'ativo' => true,
            'ordem' => 1,
            'formulario_encaminhamento_id' => $formCustom->id,
        ]);

        $resolver = new ExameFormularioResolver();
        $form = $resolver->resolverEncaminhamento($tipo->id, 7);

        $this->assertNotNull($form);
        $this->assertSame($formCustom->id, $form->id);
        $this->assertNotSame($formPadrao->id, $form->id);
    }

    public function test_tipo_admin_cria_tipo_da_empresa(): void
    {
        $resolver = new ExameFormularioResolver();
        $service = new ExameTipoAdminService($resolver);

        $tipo = $service->criar([
            'label' => 'Retorno Especial',
            'ativo' => true,
            'ordem' => 5,
        ], 11);

        $this->assertSame(11, $tipo->empresa_id);
        $this->assertSame('Retorno Especial', $tipo->label);
        $this->assertTrue($tipo->ativo);
    }

    public function test_builder_adiciona_campo_preservando_id(): void
    {
        $resolver = new ExameFormularioResolver();
        $builder = new ExameFormularioBuilderService($resolver);

        $form = $builder->criarFormulario(11, 'Exames Teste');
        $setorId = $form->Setores->first()->id;

        $campo = $builder->adicionarCampo($setorId, 11, [
            'nome' => 'Campo X',
            'tipo' => 'text',
            'obrigatorio' => true,
        ]);

        $this->assertNotNull($campo->id);
        $this->assertSame('Campo X', $campo->nome);

        $atualizado = $builder->atualizarCampo($campo->id, 11, [
            'nome' => 'Campo X Renomeado',
            'tipo' => 'text',
            'setor_id' => $setorId,
            'obrigatorio' => false,
        ]);

        $this->assertSame($campo->id, $atualizado->id);
        $this->assertSame('Campo X Renomeado', $atualizado->nome);
    }

    public function test_catalogo_montar_snapshot_preserva_labels_da_empresa(): void
    {
        \DB::table('exames')->insert([
            ['id' => 1, 'empresa_id' => 7, 'exame_tipo_id' => null, 'label' => 'Audiometria', 'ativo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'empresa_id' => 7, 'exame_tipo_id' => 1, 'label' => 'Hemograma', 'ativo' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'empresa_id' => 99, 'exame_tipo_id' => null, 'label' => 'Outro Tenant', 'ativo' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $service = new \App\Domain\Exames\Services\ExameCatalogoService();
        $snap = $service->montarSnapshot(7, [2, 1, 3, 1]);

        $this->assertCount(2, $snap);
        $this->assertSame(['id' => 1, 'label' => 'Audiometria'], $snap[0]);
        $this->assertSame(['id' => 2, 'label' => 'Hemograma'], $snap[1]);
    }

    private function criarSchema(): void
    {
        Schema::dropIfExists('setor_alternativas');
        Schema::dropIfExists('formulario_setores');
        Schema::dropIfExists('resposta_alternativas');
        Schema::dropIfExists('alternativa_formularios');
        Schema::dropIfExists('setores_formularios');
        Schema::dropIfExists('formularios');
        Schema::dropIfExists('exame_tipos');
        Schema::dropIfExists('exames');
        Schema::dropIfExists('activity_log');

        Schema::create('formularios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('titulo');
            $table->string('descricao')->nullable();
        });

        Schema::create('setores_formularios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome');
        });

        Schema::create('formulario_setores', function (Blueprint $table) {
            $table->unsignedBigInteger('formulario_id');
            $table->unsignedBigInteger('setores_id');
            $table->integer('ordem');
        });

        Schema::create('alternativa_formularios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome');
            $table->string('tipo')->default('checkbox');
            $table->boolean('ativo')->default(true);
            $table->string('chave_canonica', 64)->nullable();
        });

        Schema::create('setor_alternativas', function (Blueprint $table) {
            $table->unsignedBigInteger('setor_id');
            $table->unsignedBigInteger('alternativa_id');
            $table->boolean('obrigatorio')->default(0);
            $table->integer('min')->nullable();
            $table->integer('max')->nullable();
            $table->integer('ordem');
            $table->string('class_especial')->nullable();
        });

        Schema::create('resposta_alternativas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alternativa_id');
            $table->string('label');
            $table->boolean('selecionado')->nullable();
            $table->unsignedBigInteger('link_id')->nullable();
            $table->integer('ordem')->default(0);
            $table->bigInteger('value')->nullable();
        });

        Schema::create('exame_tipos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('label');
            $table->boolean('ativo')->default(true);
            $table->unsignedBigInteger('formulario_encaminhamento_id')->nullable();
            $table->unsignedBigInteger('formulario_resultado_id')->nullable();
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        Schema::create('exames', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('exame_tipo_id')->nullable();
            $table->string('label');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }
}
