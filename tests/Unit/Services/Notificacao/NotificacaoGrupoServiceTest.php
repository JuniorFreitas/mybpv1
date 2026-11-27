<?php

namespace Tests\Unit\Services\Notificacao;

use App\Models\NotificacaoTipo;
use App\Services\Notificacao\NotificacaoGrupoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class NotificacaoGrupoServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->criarSchema();
        $this->seedTipo();
    }

    public function test_rejeita_user_sem_privilegio_rh(): void
    {
        $userId = (int) DB::table('users')->insertGetId([
            'empresa_id' => 10,
            'nome' => 'Sem RH',
            'login' => 'semrh@example.com',
            'ativo' => 1,
            'tipo' => 'User',
            'grupo_id' => 1,
            'deleted_at' => null,
        ]);

        // Papel sem habilidades de RH → idsUsuariosRhEmpresa fica vazio
        DB::table('papeis')->insert(['id' => 1, 'nome' => 'Comum']);

        $this->expectException(ValidationException::class);

        app(NotificacaoGrupoService::class)->criar(10, NotificacaoTipo::APROVACAO_RH, [
            'nome' => 'Grupo RH',
            'ativo' => true,
            'escopo_cc' => NotificacaoTipo::ESCOPO_TODOS,
            'user_ids' => [$userId],
            'centro_custo_ids' => [],
        ]);
    }

    public function test_rejeita_tipo_nao_editavel(): void
    {
        DB::table('notificacao_tipos')->insert([
            'codigo' => 'vencimento_aso',
            'nome' => 'Vencimento ASO',
            'canal' => 'email',
            'regra_elegibilidade' => null,
            'ativo' => 1,
            'editavel' => 0,
            'ordem' => 20,
        ]);

        $this->expectException(ValidationException::class);

        app(NotificacaoGrupoService::class)->criar(10, 'vencimento_aso', [
            'nome' => 'ASO',
            'ativo' => true,
            'escopo_cc' => NotificacaoTipo::ESCOPO_TODOS,
            'user_ids' => [],
            'centro_custo_ids' => [],
        ]);
    }

    public function test_especificos_exige_centros(): void
    {
        $this->expectException(ValidationException::class);

        app(NotificacaoGrupoService::class)->criar(10, NotificacaoTipo::APROVACAO_RH, [
            'nome' => 'Especificos vazio',
            'ativo' => true,
            'escopo_cc' => NotificacaoTipo::ESCOPO_ESPECIFICOS,
            'user_ids' => [],
            'centro_custo_ids' => [],
        ]);
    }

    public function test_excluir_faz_soft_delete_e_mantem_destinatarios(): void
    {
        $papelId = (int) DB::table('papeis')->insertGetId(['nome' => 'RH']);
        $habId = (int) DB::table('habilidades')->insertGetId([
            'nome' => 'privilegio_gestao_rh',
        ]);
        DB::table('papeis_habilidades')->insert([
            'papel_id' => $papelId,
            'habilidade_id' => $habId,
        ]);
        $userId = (int) DB::table('users')->insertGetId([
            'empresa_id' => 10,
            'nome' => 'RH User',
            'login' => 'rh@example.com',
            'ativo' => 1,
            'tipo' => 'User',
            'grupo_id' => $papelId,
            'deleted_at' => null,
        ]);

        $grupo = app(NotificacaoGrupoService::class)->criar(10, NotificacaoTipo::APROVACAO_RH, [
            'nome' => 'Grupo Soft',
            'ativo' => true,
            'escopo_cc' => NotificacaoTipo::ESCOPO_TODOS,
            'user_ids' => [$userId],
            'centro_custo_ids' => [],
        ]);

        app(NotificacaoGrupoService::class)->excluir($grupo);

        $this->assertSoftDeleted('notificacao_grupos', ['id' => $grupo->id]);
        $this->assertDatabaseHas('notificacao_grupo_destinatarios', [
            'grupo_id' => $grupo->id,
            'user_id' => $userId,
        ]);
        $this->assertNull(
            \App\Models\NotificacaoGrupo::query()->withoutGlobalScopes()->whereNull('deleted_at')->find($grupo->id)
        );
    }

    private function criarSchema(): void
    {
        Schema::dropIfExists('notificacao_grupo_centros_custo');
        Schema::dropIfExists('notificacao_grupo_destinatarios');
        Schema::dropIfExists('notificacao_grupos');
        Schema::dropIfExists('notificacao_tipos');
        Schema::dropIfExists('papeis_habilidades');
        Schema::dropIfExists('habilidades');
        Schema::dropIfExists('papeis');
        Schema::dropIfExists('centro_custos');
        Schema::dropIfExists('users');

        Schema::create('notificacao_tipos', function (Blueprint $table) {
            $table->string('codigo', 64)->primary();
            $table->string('nome', 120);
            $table->string('canal', 32)->default('email');
            $table->string('regra_elegibilidade', 64)->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('editavel')->default(false);
            $table->unsignedSmallInteger('ordem')->default(100);
        });

        Schema::create('notificacao_grupos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id');
            $table->string('tipo_codigo', 64);
            $table->string('nome', 160);
            $table->boolean('ativo')->default(true);
            $table->string('escopo_cc', 32)->default('todos');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('notificacao_grupo_destinatarios', function (Blueprint $table) {
            $table->unsignedInteger('grupo_id');
            $table->unsignedInteger('user_id');
            $table->primary(['grupo_id', 'user_id']);
        });

        Schema::create('notificacao_grupo_centros_custo', function (Blueprint $table) {
            $table->unsignedInteger('grupo_id');
            $table->unsignedInteger('centro_custo_id');
            $table->primary(['grupo_id', 'centro_custo_id']);
        });

        Schema::create('centro_custos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id')->nullable();
            $table->string('label')->nullable();
            $table->boolean('ativo')->default(true);
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id')->nullable();
            $table->unsignedInteger('grupo_id')->nullable();
            $table->string('nome')->nullable();
            $table->string('login')->nullable();
            $table->boolean('ativo')->default(true);
            $table->string('tipo')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create('papeis', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome')->nullable();
        });

        Schema::create('habilidades', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
        });

        Schema::create('papeis_habilidades', function (Blueprint $table) {
            $table->unsignedInteger('papel_id');
            $table->unsignedInteger('habilidade_id');
        });
    }

    private function seedTipo(): void
    {
        DB::table('notificacao_tipos')->insert([
            'codigo' => NotificacaoTipo::APROVACAO_RH,
            'nome' => 'Aprovação RH',
            'canal' => 'email',
            'regra_elegibilidade' => NotificacaoTipo::REGRA_PRIVILEGIOS_RH,
            'ativo' => 1,
            'editavel' => 1,
            'ordem' => 10,
        ]);
    }
}
