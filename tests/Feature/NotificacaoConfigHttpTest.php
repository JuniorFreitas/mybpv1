<?php

namespace Tests\Feature;

use App\Models\NotificacaoTipo;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Feature tests HTTP da API Configurações › Notificações.
 * Banco: SQLite :memory: (phpunit.xml).
 */
class NotificacaoConfigHttpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        $this->garantirTabelas();
        $this->seedTipos();
        $this->registrarGates(true);
        $this->withoutMiddleware();
    }

    public function test_listar_sem_grupos_retorna_aviso(): void
    {
        $this->actingAs($this->criarUsuario(['empresa_id' => 10]));

        $response = $this->getJson('/g/configuracoes/notificacoes/grupos?tipo_codigo=aprovacao_rh');

        $response->assertOk()
            ->assertJsonPath('aviso_sem_grupos', true)
            ->assertJsonPath('paginacao.total', 0);
        $this->assertSame([], $response->json('itens'));
    }

    public function test_store_cria_grupo_todos_sem_destinatarios(): void
    {
        $this->actingAs($this->criarUsuario(['empresa_id' => 10]));

        $response = $this->postJson('/g/configuracoes/notificacoes/grupos', [
            'tipo_codigo' => NotificacaoTipo::APROVACAO_RH,
            'nome' => 'RH Geral',
            'ativo' => true,
            'escopo_cc' => NotificacaoTipo::ESCOPO_TODOS,
            'user_ids' => [],
            'centro_custo_ids' => [],
        ]);

        $response->assertCreated()
            ->assertJsonPath('item.nome', 'RH Geral')
            ->assertJsonPath('item.escopo_cc', 'todos')
            ->assertJsonPath('item.ativo', true);

        $this->assertDatabaseHas('notificacao_grupos', [
            'empresa_id' => 10,
            'tipo_codigo' => NotificacaoTipo::APROVACAO_RH,
            'nome' => 'RH Geral',
        ]);
    }

    public function test_store_com_destinatario_rh_e_listagem_reflete(): void
    {
        $this->actingAs($this->criarUsuario(['empresa_id' => 10]));
        $rhId = $this->criarUsuarioRh(10, 'rh@example.com');

        $response = $this->postJson('/g/configuracoes/notificacoes/grupos', [
            'tipo_codigo' => NotificacaoTipo::APROVACAO_RH,
            'nome' => 'RH Matriz',
            'ativo' => true,
            'escopo_cc' => NotificacaoTipo::ESCOPO_TODOS,
            'user_ids' => [$rhId],
            'centro_custo_ids' => [],
        ]);

        $response->assertCreated()
            ->assertJsonPath('item.destinatarios_count', 1);

        $lista = $this->getJson('/g/configuracoes/notificacoes/grupos?tipo_codigo=aprovacao_rh');
        $lista->assertOk()
            ->assertJsonPath('aviso_sem_grupos', false)
            ->assertJsonPath('paginacao.total', 1);
    }

    public function test_store_rejeita_user_sem_privilegio_rh(): void
    {
        $this->actingAs($this->criarUsuario(['empresa_id' => 10]));
        $comumId = $this->criarUsuarioPersistido([
            'empresa_id' => 10,
            'login' => 'comum@example.com',
            'grupo_id' => 99,
        ]);
        DB::table('papeis')->insertOrIgnore(['id' => 99, 'nome' => 'Comum']);

        $response = $this->postJson('/g/configuracoes/notificacoes/grupos', [
            'tipo_codigo' => NotificacaoTipo::APROVACAO_RH,
            'nome' => 'Inválido',
            'ativo' => true,
            'escopo_cc' => NotificacaoTipo::ESCOPO_TODOS,
            'user_ids' => [$comumId],
            'centro_custo_ids' => [],
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('notificacao_grupos', ['nome' => 'Inválido']);
    }

    public function test_store_rejeita_tipo_nao_editavel(): void
    {
        $this->actingAs($this->criarUsuario(['empresa_id' => 10]));

        $response = $this->postJson('/g/configuracoes/notificacoes/grupos', [
            'tipo_codigo' => 'vencimento_aso',
            'nome' => 'ASO',
            'ativo' => true,
            'escopo_cc' => NotificacaoTipo::ESCOPO_TODOS,
            'user_ids' => [],
            'centro_custo_ids' => [],
        ]);

        $response->assertStatus(422);
    }

    public function test_especificos_exige_centros_e_aceita_cc_da_empresa(): void
    {
        $this->actingAs($this->criarUsuario(['empresa_id' => 10]));
        $ccId = (int) DB::table('centro_custos')->insertGetId([
            'empresa_id' => 10,
            'label' => 'CC 1',
            'ativo' => 1,
        ]);

        $semCc = $this->postJson('/g/configuracoes/notificacoes/grupos', [
            'tipo_codigo' => NotificacaoTipo::APROVACAO_RH,
            'nome' => 'Sem CC',
            'ativo' => true,
            'escopo_cc' => NotificacaoTipo::ESCOPO_ESPECIFICOS,
            'user_ids' => [],
            'centro_custo_ids' => [],
        ]);
        $semCc->assertStatus(422);

        $comCc = $this->postJson('/g/configuracoes/notificacoes/grupos', [
            'tipo_codigo' => NotificacaoTipo::APROVACAO_RH,
            'nome' => 'Com CC',
            'ativo' => true,
            'escopo_cc' => NotificacaoTipo::ESCOPO_ESPECIFICOS,
            'user_ids' => [],
            'centro_custo_ids' => [$ccId],
        ]);
        $comCc->assertCreated()->assertJsonPath('item.nome', 'Com CC');
    }

    public function test_show_update_destroy_respeitam_tenant(): void
    {
        $this->actingAs($this->criarUsuario(['empresa_id' => 10]));

        $grupoOutro = (int) DB::table('notificacao_grupos')->insertGetId([
            'empresa_id' => 99,
            'tipo_codigo' => NotificacaoTipo::APROVACAO_RH,
            'nome' => 'Outra empresa',
            'ativo' => 1,
            'escopo_cc' => 'todos',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson("/g/configuracoes/notificacoes/grupos/{$grupoOutro}")->assertNotFound();
        $this->putJson("/g/configuracoes/notificacoes/grupos/{$grupoOutro}", [
            'nome' => 'Hack',
            'ativo' => true,
            'escopo_cc' => 'todos',
            'user_ids' => [],
            'centro_custo_ids' => [],
        ])->assertNotFound();
        $this->deleteJson("/g/configuracoes/notificacoes/grupos/{$grupoOutro}")->assertNotFound();

        $criar = $this->postJson('/g/configuracoes/notificacoes/grupos', [
            'tipo_codigo' => NotificacaoTipo::APROVACAO_RH,
            'nome' => 'Meu grupo',
            'ativo' => true,
            'escopo_cc' => NotificacaoTipo::ESCOPO_TODOS,
            'user_ids' => [],
            'centro_custo_ids' => [],
        ]);
        $criar->assertCreated();
        $id = (int) $criar->json('item.id');

        $this->getJson("/g/configuracoes/notificacoes/grupos/{$id}")
            ->assertOk()
            ->assertJsonPath('item.nome', 'Meu grupo');

        $this->putJson("/g/configuracoes/notificacoes/grupos/{$id}", [
            'nome' => 'Meu grupo editado',
            'ativo' => false,
            'escopo_cc' => NotificacaoTipo::ESCOPO_MATRIZ,
            'user_ids' => [],
            'centro_custo_ids' => [],
        ])->assertOk()->assertJsonPath('item.nome', 'Meu grupo editado')
            ->assertJsonPath('item.ativo', false)
            ->assertJsonPath('item.escopo_cc', 'matriz');

        $this->deleteJson("/g/configuracoes/notificacoes/grupos/{$id}")->assertNoContent();
        $this->assertSoftDeleted('notificacao_grupos', ['id' => $id]);
    }

    public function test_ativa_desativa_alterna_status(): void
    {
        $this->actingAs($this->criarUsuario(['empresa_id' => 10]));

        $criar = $this->postJson('/g/configuracoes/notificacoes/grupos', [
            'tipo_codigo' => NotificacaoTipo::APROVACAO_RH,
            'nome' => 'Toggle grupo',
            'ativo' => true,
            'escopo_cc' => NotificacaoTipo::ESCOPO_TODOS,
            'user_ids' => [],
            'centro_custo_ids' => [],
        ]);
        $criar->assertCreated();
        $id = (int) $criar->json('item.id');

        $this->putJson("/g/configuracoes/notificacoes/grupos/{$id}/ativa-desativa")
            ->assertOk()
            ->assertJsonPath('ativo', false);

        $this->assertDatabaseHas('notificacao_grupos', ['id' => $id, 'ativo' => 0]);

        $this->putJson("/g/configuracoes/notificacoes/grupos/{$id}/ativa-desativa")
            ->assertOk()
            ->assertJsonPath('ativo', true);

        $this->assertDatabaseHas('notificacao_grupos', ['id' => $id, 'ativo' => 1]);
    }

    public function test_candidatos_retorna_somente_rh(): void
    {
        $this->actingAs($this->criarUsuario(['empresa_id' => 10]));
        $rhId = $this->criarUsuarioRh(10, 'rh2@example.com');
        $this->criarUsuarioPersistido([
            'empresa_id' => 10,
            'login' => 'nao-rh@example.com',
            'grupo_id' => 50,
        ]);
        DB::table('papeis')->insertOrIgnore(['id' => 50, 'nome' => 'Sem skill']);

        $response = $this->getJson('/g/configuracoes/notificacoes/candidatos?tipo_codigo=aprovacao_rh');

        $response->assertOk();
        $ids = collect($response->json('candidatos'))->pluck('id')->all();
        $this->assertContains($rhId, $ids);
        $this->assertCount(1, $ids);
    }

    public function test_tipos_lista_catalogo(): void
    {
        $this->actingAs($this->criarUsuario(['empresa_id' => 10]));

        $response = $this->getJson('/g/configuracoes/notificacoes/tipos');

        $response->assertOk();
        $codigos = collect($response->json('tipos'))->pluck('codigo')->all();
        $this->assertContains('aprovacao_rh', $codigos);
        $this->assertContains('vencimento_aso', $codigos);
    }

    public function test_store_sem_permissao_retorna_403(): void
    {
        $this->registrarGates(false);
        $this->actingAs($this->criarUsuario(['empresa_id' => 10]));

        $this->postJson('/g/configuracoes/notificacoes/grupos', [
            'tipo_codigo' => NotificacaoTipo::APROVACAO_RH,
            'nome' => 'Bloqueado',
            'ativo' => true,
            'escopo_cc' => NotificacaoTipo::ESCOPO_TODOS,
            'user_ids' => [],
            'centro_custo_ids' => [],
        ])->assertForbidden();
    }

    private function garantirTabelas(): void
    {
        Schema::dropIfExists('notificacao_grupo_centros_custo');
        Schema::dropIfExists('notificacao_grupo_destinatarios');
        Schema::dropIfExists('notificacao_grupos');
        Schema::dropIfExists('notificacao_tipos');
        Schema::dropIfExists('centro_custo_filials');
        Schema::dropIfExists('centro_custos');
        Schema::dropIfExists('papeis_habilidades');
        Schema::dropIfExists('habilidades');
        Schema::dropIfExists('papeis');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id')->nullable();
            $table->unsignedInteger('grupo_id')->nullable();
            $table->string('nome')->nullable();
            $table->string('login')->nullable();
            $table->boolean('ativo')->default(true);
            $table->string('tipo')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('papeis', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome')->nullable();
        });

        Schema::create('habilidades', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
            $table->string('descricao')->nullable();
        });

        Schema::create('papeis_habilidades', function (Blueprint $table) {
            $table->unsignedInteger('papel_id');
            $table->unsignedInteger('habilidade_id');
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

        Schema::create('centro_custos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id')->nullable();
            $table->string('label')->nullable();
            $table->boolean('ativo')->default(true);
        });

        Schema::create('centro_custo_filials', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('empresa_id')->nullable();
            $table->unsignedInteger('centro_custo_id');
            $table->unsignedInteger('cliente_filial_id')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    private function seedTipos(): void
    {
        foreach ([
            ['codigo' => 'aprovacao_rh', 'nome' => 'Aprovação RH', 'canal' => 'email', 'regra_elegibilidade' => 'privilegios_rh', 'ativo' => 1, 'editavel' => 1, 'ordem' => 10],
            ['codigo' => 'vencimento_aso', 'nome' => 'Vencimento ASO', 'canal' => 'email', 'regra_elegibilidade' => null, 'ativo' => 1, 'editavel' => 0, 'ordem' => 20],
            ['codigo' => 'vencimento_ferias', 'nome' => 'Vencimento Férias', 'canal' => 'email', 'regra_elegibilidade' => null, 'ativo' => 1, 'editavel' => 0, 'ordem' => 30],
        ] as $tipo) {
            DB::table('notificacao_tipos')->insert($tipo);
        }
    }

    private function registrarGates(bool $pode = true): void
    {
        Gate::define('configuracao_notificacoes', fn () => $pode);
    }

    /** @param array<string, mixed> $dados */
    private function criarUsuario(array $dados = []): User
    {
        $attrs = array_merge([
            'id' => 1,
            'nome' => 'Admin Teste',
            'login' => 'admin@test.com',
            'tipo' => 'ADMINISTRADOR',
            'ativo' => true,
            'empresa_id' => 10,
        ], $dados);

        $user = new User($attrs);
        $user->id = $attrs['id'];
        $user->exists = true;

        return $user;
    }

    /** @param array<string, mixed> $dados */
    private function criarUsuarioPersistido(array $dados = []): int
    {
        return (int) DB::table('users')->insertGetId(array_merge([
            'nome' => 'User',
            'login' => 'user' . uniqid('', true) . '@example.com',
            'tipo' => 'User',
            'ativo' => 1,
            'empresa_id' => 10,
            'grupo_id' => null,
            'deleted_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $dados));
    }

    private function criarUsuarioRh(int $empresaId, string $login): int
    {
        $papelId = 1;
        if (!DB::table('papeis')->where('id', $papelId)->exists()) {
            DB::table('papeis')->insert(['id' => $papelId, 'nome' => 'RH']);
        }

        $habId = DB::table('habilidades')->where('nome', 'privilegio_gestao_rh')->value('id');
        if (!$habId) {
            $habId = DB::table('habilidades')->insertGetId([
                'nome' => 'privilegio_gestao_rh',
                'descricao' => 'RH',
            ]);
        }

        $linked = DB::table('papeis_habilidades')
            ->where('papel_id', $papelId)
            ->where('habilidade_id', $habId)
            ->exists();
        if (!$linked) {
            DB::table('papeis_habilidades')->insert([
                'papel_id' => $papelId,
                'habilidade_id' => $habId,
            ]);
        }

        return $this->criarUsuarioPersistido([
            'empresa_id' => $empresaId,
            'login' => $login,
            'nome' => 'Usuário RH',
            'grupo_id' => $papelId,
            'tipo' => 'User',
            'ativo' => 1,
        ]);
    }
}
