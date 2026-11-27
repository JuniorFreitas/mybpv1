<?php

namespace Tests\Unit\Services\Notificacao;

use App\Models\NotificacaoTipo;
use App\Services\Notificacao\LiberarNotificacaoEmpresaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LiberarNotificacaoEmpresaServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->criarSchema();
        $this->seedBase();
    }

    public function test_libera_habilidade_e_cria_grupo_com_destinatarios_rh(): void
    {
        $resultado = app(LiberarNotificacaoEmpresaService::class)->liberar(10);

        $this->assertNotEmpty($resultado['papeis_liberados']);
        $this->assertContains(1, $resultado['papeis_liberados']);
        $this->assertTrue($resultado['grupo_criado']);
        $this->assertNotNull($resultado['grupo_id']);
        $this->assertSame(1, $resultado['destinatarios']);

        $this->assertDatabaseHas('papeis_habilidades', [
            'papel_id' => 1,
            'habilidade_id' => $resultado['habilidade_id'],
        ]);
        $this->assertDatabaseHas('notificacao_grupos', [
            'empresa_id' => 10,
            'nome' => LiberarNotificacaoEmpresaService::GRUPO_PADRAO,
            'tipo_codigo' => NotificacaoTipo::APROVACAO_RH,
        ]);
    }

    public function test_segunda_chamada_e_idempotente(): void
    {
        $svc = app(LiberarNotificacaoEmpresaService::class);
        $svc->liberar(10);
        $segunda = $svc->liberar(10);

        $this->assertSame([], $segunda['papeis_liberados']);
        $this->assertFalse($segunda['grupo_criado']);
        $this->assertSame(1, $segunda['destinatarios']);
    }

    private function criarSchema(): void
    {
        Schema::dropIfExists('notificacao_grupo_destinatarios');
        Schema::dropIfExists('notificacao_grupo_centros_custo');
        Schema::dropIfExists('notificacao_grupos');
        Schema::dropIfExists('notificacao_tipos');
        Schema::dropIfExists('papeis_habilidades');
        Schema::dropIfExists('habilidades');
        Schema::dropIfExists('papeis');
        Schema::dropIfExists('users');

        Schema::create('habilidades', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
            $table->string('descricao')->nullable();
        });

        Schema::create('papeis', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome')->nullable();
            $table->unsignedInteger('empresa_id')->nullable();
            $table->boolean('ativo')->default(true);
        });

        Schema::create('papeis_habilidades', function (Blueprint $table) {
            $table->unsignedInteger('papel_id');
            $table->unsignedInteger('habilidade_id');
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
    }

    private function seedBase(): void
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

        $habRh = (int) DB::table('habilidades')->insertGetId([
            'nome' => 'privilegio_gestao_rh',
            'descricao' => 'RH',
        ]);
        $habCfg = (int) DB::table('habilidades')->insertGetId([
            'nome' => 'configuracao_papel',
            'descricao' => 'Config papel',
        ]);

        DB::table('papeis')->insert([
            'id' => 1,
            'nome' => 'Admin',
            'empresa_id' => 10,
            'ativo' => 1,
        ]);
        DB::table('papeis_habilidades')->insert([
            ['papel_id' => 1, 'habilidade_id' => $habRh],
            ['papel_id' => 1, 'habilidade_id' => $habCfg],
        ]);

        DB::table('users')->insert([
            'empresa_id' => 10,
            'grupo_id' => 1,
            'nome' => 'RH',
            'login' => 'rh@example.com',
            'ativo' => 1,
            'tipo' => 'User',
            'deleted_at' => null,
        ]);
    }
}
