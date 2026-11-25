<?php

namespace Tests\Feature;

use App\Models\AprovacaoExtraConfig;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AprovacaoExtraConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        $this->garantirTabelas();
        $this->registrarGates(true);
        $this->withoutMiddleware();
    }

    public function test_listar_retorna_somente_configs_da_empresa(): void
    {
        $user = $this->criarUsuario(['empresa_id' => 10]);
        $this->actingAs($user);

        $this->criarConfig([
            'empresa_id' => 10,
            'tipo_processo' => AprovacaoExtraConfig::TIPO_DEMISSAO,
            'nome_aprovacao' => 'Extra Demissão',
            'usuarios_autorizados' => [1],
            'ativo' => true,
        ]);
        $this->criarConfig([
            'empresa_id' => 99,
            'tipo_processo' => AprovacaoExtraConfig::TIPO_FERIAS,
            'nome_aprovacao' => 'Outra Empresa',
            'usuarios_autorizados' => [],
            'ativo' => true,
        ]);

        $response = $this->getJson('/g/administracao/aprovacao-extra-config/listar');

        $response->assertOk();
        $dados = $response->json();
        $this->assertCount(1, $dados);
        $this->assertSame('Extra Demissão', $dados[0]['nome_aprovacao']);
    }

    public function test_store_cria_configuracao(): void
    {
        $user = $this->criarUsuario(['empresa_id' => 10, 'id' => 7]);
        $this->actingAs($user);
        $this->persistirUsuario($user);

        $response = $this->postJson('/g/administracao/aprovacao-extra-config', [
            'tipo_processo' => AprovacaoExtraConfig::TIPO_FERIAS,
            'nome_aprovacao' => 'Aprovação Extra Férias',
            'usuarios_autorizados' => [7],
            'ativo' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('aprovacao_extra_configs', [
            'empresa_id' => 10,
            'tipo_processo' => AprovacaoExtraConfig::TIPO_FERIAS,
            'nome_aprovacao' => 'Aprovação Extra Férias',
            'ativo' => 1,
        ]);
    }

    public function test_store_rejeita_duplicidade_do_mesmo_tipo(): void
    {
        $user = $this->criarUsuario(['empresa_id' => 10]);
        $this->actingAs($user);

        $this->criarConfig([
            'empresa_id' => 10,
            'tipo_processo' => AprovacaoExtraConfig::TIPO_DEMISSAO,
            'nome_aprovacao' => 'Já existe',
            'usuarios_autorizados' => [],
            'ativo' => true,
        ]);

        $response = $this->postJson('/g/administracao/aprovacao-extra-config', [
            'tipo_processo' => AprovacaoExtraConfig::TIPO_DEMISSAO,
            'nome_aprovacao' => 'Duplicada',
            'usuarios_autorizados' => [],
            'ativo' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_pode_aprovar_usuario_autorizado(): void
    {
        $this->garantirTabelasPrivilegios();
        $user = $this->criarUsuario(['empresa_id' => 10, 'id' => 42, 'grupo_id' => 1]);
        $this->actingAs($user);
        $this->persistirUsuario($user);

        $this->criarConfig([
            'empresa_id' => 10,
            'tipo_processo' => AprovacaoExtraConfig::TIPO_TRANSFERENCIA,
            'nome_aprovacao' => 'Extra Transferência',
            'usuarios_autorizados' => [42],
            'ativo' => true,
        ]);

        $response = $this->postJson('/g/administracao/aprovacao-extra-config/pode-aprovar', [
            'tipo_processo' => AprovacaoExtraConfig::TIPO_TRANSFERENCIA,
        ]);

        $response->assertOk()
            ->assertJsonPath('pode_aprovar', true)
            ->assertJsonPath('nome_aprovacao', 'Extra Transferência');
    }

    public function test_pode_aprovar_usuario_nao_autorizado(): void
    {
        $this->garantirTabelasPrivilegios();
        $user = $this->criarUsuario(['empresa_id' => 10, 'id' => 42, 'grupo_id' => 1]);
        $this->actingAs($user);
        $this->persistirUsuario($user);

        $this->criarConfig([
            'empresa_id' => 10,
            'tipo_processo' => AprovacaoExtraConfig::TIPO_TRANSFERENCIA,
            'nome_aprovacao' => 'Extra Transferência',
            'usuarios_autorizados' => [99],
            'ativo' => true,
        ]);

        $response = $this->postJson('/g/administracao/aprovacao-extra-config/pode-aprovar', [
            'tipo_processo' => AprovacaoExtraConfig::TIPO_TRANSFERENCIA,
        ]);

        $response->assertOk()
            ->assertJsonPath('pode_aprovar', false);
    }

    public function test_update_e_destroy_isolados_por_tenant(): void
    {
        $user = $this->criarUsuario(['empresa_id' => 10]);
        $this->actingAs($user);

        $outra = $this->criarConfig([
            'empresa_id' => 99,
            'tipo_processo' => AprovacaoExtraConfig::TIPO_VALOR_EXTRA,
            'nome_aprovacao' => 'Outra',
            'usuarios_autorizados' => [],
            'ativo' => true,
        ]);

        $this->putJson("/g/administracao/aprovacao-extra-config/{$outra->id}", [
            'nome_aprovacao' => 'Hack',
            'usuarios_autorizados' => [],
            'ativo' => false,
        ])->assertStatus(500)->assertJsonPath('success', false);

        $this->deleteJson("/g/administracao/aprovacao-extra-config/{$outra->id}")
            ->assertStatus(500)->assertJsonPath('success', false);

        $this->assertDatabaseHas('aprovacao_extra_configs', [
            'id' => $outra->id,
            'empresa_id' => 99,
            'nome_aprovacao' => 'Outra',
            'ativo' => 1,
        ]);
    }

    public function test_tipos_processo_inclui_mudanca_cargo(): void
    {
        $user = $this->criarUsuario(['empresa_id' => 10]);
        $this->actingAs($user);

        $response = $this->getJson('/g/administracao/aprovacao-extra-config/tipos-processo');

        $response->assertOk()
            ->assertJsonPath('tipos.mudanca_cargo', 'Mudança de Cargo');
    }

    private function garantirTabelas(): void
    {
        Schema::dropIfExists('aprovacao_extra_configs');
        Schema::dropIfExists('users');

        Schema::create('aprovacao_extra_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('tipo_processo');
            $table->string('nome_aprovacao');
            $table->json('usuarios_autorizados')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('login')->nullable();
            $table->string('tipo')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('grupo_id')->nullable();
            $table->boolean('ativo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function criarConfig(array $dados): AprovacaoExtraConfig
    {
        $payload = array_merge([
            'usuarios_autorizados' => [],
            'ativo' => true,
        ], $dados);

        if (is_array($payload['usuarios_autorizados'])) {
            $payload['usuarios_autorizados'] = json_encode($payload['usuarios_autorizados']);
        }

        $id = \Illuminate\Support\Facades\DB::table('aprovacao_extra_configs')->insertGetId(array_merge($payload, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return AprovacaoExtraConfig::withoutGlobalScopes()->findOrFail($id);
    }

    private function garantirTabelasPrivilegios(): void
    {
        Schema::dropIfExists('papeis_habilidades');
        Schema::dropIfExists('habilidades');
        Schema::dropIfExists('papeis');

        Schema::create('papeis', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
        });
        Schema::create('habilidades', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
        });
        Schema::create('papeis_habilidades', function (Blueprint $table) {
            $table->unsignedBigInteger('papel_id');
            $table->unsignedBigInteger('habilidade_id');
        });
    }

    private function registrarGates(bool $permitir): void
    {
        Gate::define('cadastro_customizacoes_aprovacao_extra', fn () => $permitir);
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function criarUsuario(array $dados = []): User
    {
        $attrs = array_merge([
            'id' => 1,
            'nome' => 'Usuário Teste',
            'login' => 'teste',
            'tipo' => 'ADMINISTRADOR',
            'ativo' => true,
            'empresa_id' => 10,
        ], $dados);

        $user = new User($attrs);
        $user->id = $attrs['id'];
        $user->exists = true;

        return $user;
    }

    private function persistirUsuario(User $user): void
    {
        \Illuminate\Support\Facades\DB::table('users')->updateOrInsert(
            ['id' => $user->id],
            [
                'nome' => $user->nome,
                'login' => $user->login ?? 'teste',
                'tipo' => $user->tipo,
                'empresa_id' => $user->empresa_id,
                'grupo_id' => $user->grupo_id ?? null,
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
