<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Cenários de segurança e regressão para GET/PUT /g/perfil/{id}
 * (UserController::perfilUsuario / atualizaPerfilUsuario).
 *
 * Ref.: relatório de auditoria de segurança — Vuln 1 (mass assignment / IDOR) e
 * Vuln 2 (exposição do api_token via IDOR).
 *
 * Testes "test_seguranca_*" descrevem o comportamento esperado depois da correção
 * e FALHAM contra o código atual (vulnerável) — servem de guia (red -> green).
 * Testes "test_regressao_*" descrevem o fluxo legítimo de hoje e devem continuar
 * passando depois de qualquer correção aplicada.
 */
class PerfilUsuarioSegurancaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        $this->garantirTabelas();
        $this->withoutMiddleware();
    }

    // ---------- Vuln 1: mass assignment / IDOR em atualizaPerfilUsuario ----------

    public function test_seguranca_usuario_nao_altera_grupo_empresa_ou_senha_do_proprio_perfil(): void
    {
        $usuario = $this->criarUsuario([
            'grupo_id' => 5,
            'empresa_id' => 10,
            'password' => bcrypt('senha-original'),
        ]);
        $senhaOriginal = $usuario->password;
        $this->actingAs($usuario);

        $this->putJson("/g/perfil/{$usuario->id}", [
            'nome' => 'Nome Atualizado',
            'login' => $usuario->login,
            'grupo_id' => 1, // tentativa de virar grupo administrativo
            'empresa_id' => 999, // tentativa de mudar de empresa
            'password' => 'senha-invadida',
        ]);

        $usuario->refresh();
        $this->assertSame(5, $usuario->grupo_id, 'grupo_id não deveria ser alterável pelo próprio usuário via /g/perfil');
        $this->assertSame(10, $usuario->empresa_id, 'empresa_id não deveria ser alterável pelo próprio usuário via /g/perfil');
        $this->assertSame($senhaOriginal, $usuario->password, 'password não deveria ser alterável sem um fluxo dedicado de troca de senha');
    }

    public function test_seguranca_usuario_nao_atualiza_perfil_de_outro_usuario(): void
    {
        $atacante = $this->criarUsuario(['empresa_id' => 10]);
        $vitima = $this->criarUsuario(['empresa_id' => 20, 'nome' => 'Vítima']);
        $this->actingAs($atacante);

        $response = $this->putJson("/g/perfil/{$vitima->id}", [
            'nome' => 'Nome Trocado Pelo Atacante',
            'login' => $vitima->login,
        ]);

        // 403 ou 404 dependendo da implementação escolhida para a correção;
        // o que importa é que a resposta não seja de sucesso.
        $this->assertContains($response->status(), [403, 404]);
        $this->assertSame('Vítima', $vitima->fresh()->nome);
    }

    public function test_regressao_usuario_atualiza_o_proprio_nome_e_login(): void
    {
        $usuario = $this->criarUsuario();
        $this->actingAs($usuario);

        $response = $this->putJson("/g/perfil/{$usuario->id}", [
            'nome' => 'Novo Nome',
            'login' => 'novo.login',
        ]);

        $response->assertStatus(201);
        $usuario->refresh();
        $this->assertSame('Novo Nome', $usuario->nome);
        $this->assertSame('novo.login', $usuario->login);
    }

    // ---------- Vuln 2: exposição do api_token / IDOR em perfilUsuario ----------

    public function test_seguranca_usuario_nao_visualiza_perfil_de_outro_usuario(): void
    {
        $atacante = $this->criarUsuario(['empresa_id' => 10]);
        $vitima = $this->criarUsuario(['empresa_id' => 20, 'api_token' => 'token-secreto-da-vitima']);
        $this->actingAs($atacante);

        $response = $this->getJson("/g/perfil/{$vitima->id}");

        $this->assertContains($response->status(), [403, 404]);
        $response->assertDontSee('token-secreto-da-vitima');
    }

    public function test_seguranca_resposta_do_proprio_perfil_nao_expoe_api_token(): void
    {
        $usuario = $this->criarUsuario(['api_token' => 'token-secreto-proprio']);
        $this->actingAs($usuario);

        $response = $this->getJson("/g/perfil/{$usuario->id}");

        $response->assertOk();
        $response->assertJsonMissingPath('user.api_token');
    }

    public function test_regressao_usuario_visualiza_o_proprio_perfil(): void
    {
        $usuario = $this->criarUsuario(['nome' => 'Fulano de Tal']);
        $this->actingAs($usuario);

        $response = $this->getJson("/g/perfil/{$usuario->id}");

        $response->assertOk();
        $response->assertJsonPath('user.nome', 'Fulano de Tal');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function criarUsuario(array $overrides = []): User
    {
        $id = DB::table('users')->insertGetId(array_merge([
            'nome' => 'Usuário Teste',
            'login' => 'usuario.' . Str::random(8),
            'password' => bcrypt('senha-padrao'),
            'tipo' => User::FUNCIONARIO,
            'grupo_id' => 5,
            'ativo' => true,
            'temp' => false,
            'empresa_id' => 10,
            'api_token' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));

        return User::find($id);
    }

    private function garantirTabelas(): void
    {
        Schema::dropIfExists('user_anexos');
        Schema::dropIfExists('arquivos');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('login')->nullable();
            $table->string('password')->nullable();
            $table->string('tipo');
            $table->unsignedBigInteger('grupo_id')->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('temp')->default(false);
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('api_token')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // usadas por User::FotoPerfil() (belongsToMany), carregada em perfilUsuario()
        Schema::create('arquivos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->timestamps();
        });

        Schema::create('user_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('arquivo_id');
        });
    }
}
