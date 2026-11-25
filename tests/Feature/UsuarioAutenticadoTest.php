<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UsuarioAutenticadoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        $this->garantirTabelas();
        $this->withoutMiddleware();

        Gate::define('configuracao_whatsapp', fn () => false);
        Gate::define('administracao_clientes', fn () => false);
        Gate::define('preferencias_notificacao_whatsapp', fn () => false);
        Gate::define('privilegio_gestao_rh', fn () => false);
    }

    public function test_get_usuario_com_pivot_fora_do_tenant_nao_vaza_area_e_nao_quebra(): void
    {
        $empresaId = 104;
        $clienteForaDoScopeId = 60331;

        DB::table('users')->insert([
            [
                'id' => 50393,
                'nome' => 'Usuario Teste',
                'login' => 'usuario.teste',
                'tipo' => 'Usuario',
                'empresa_id' => $empresaId,
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $clienteForaDoScopeId,
                'nome' => 'Cliente Fora Scope',
                'login' => 'cliente.fora',
                'tipo' => 'Empresa',
                'empresa_id' => $clienteForaDoScopeId,
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $empresaId,
                'nome' => 'Empresa Auth',
                'login' => 'empresa.auth',
                'tipo' => 'Empresa',
                'empresa_id' => $empresaId,
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('clientes')->insert([
            [
                'id' => $empresaId,
                'apelido' => 'bpse',
                'area_id' => 9,
                'cnpj' => '00000000000191',
                'nome_fantasia' => 'Empresa Auth',
                'razao_social' => 'Empresa Auth LTDA',
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $clienteForaDoScopeId,
                'apelido' => '_bpservice',
                'area_id' => 1,
                'cnpj' => '00000000000272',
                'nome_fantasia' => 'Cliente Fora',
                'razao_social' => 'Cliente Fora LTDA',
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Escopo global de Cliente: só a empresa do usuário.
        DB::table('empresa_clientes')->insert([
            'empresa_id' => $empresaId,
            'cliente_id' => $empresaId,
        ]);

        // Pivot fora do ClientesEmpresa — antes gerava 500; agora area_id=0 (sem vazamento).
        DB::table('cliente_funcionarios')->insert([
            'funcionario_id' => 50393,
            'cliente_id' => $clienteForaDoScopeId,
        ]);

        DB::table('cliente_configs')->insert([
            'cliente_id' => $empresaId,
            'envia_whatsapp' => 0,
        ]);

        $user = User::find(50393);
        $this->actingAs($user);

        $response = $this->getJson('/g/usuario/autenticado');

        $response->assertOk()
            ->assertJsonPath('user_id', 50393)
            ->assertJsonPath('empresa_id', $empresaId)
            ->assertJsonPath('area_id', 0)
            ->assertJsonPath('apelido', 'bpse');
    }

    public function test_get_usuario_com_cliente_no_tenant_retorna_area_id(): void
    {
        $empresaId = 20;
        $clienteId = 21;

        DB::table('users')->insert([
            [
                'id' => 8,
                'nome' => 'Com Tenant',
                'login' => 'com.tenant',
                'tipo' => 'Usuario',
                'empresa_id' => $empresaId,
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $empresaId,
                'nome' => 'Empresa 20',
                'login' => 'empresa.20',
                'tipo' => 'Empresa',
                'empresa_id' => $empresaId,
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $clienteId,
                'nome' => 'Cliente 21',
                'login' => 'cliente.21',
                'tipo' => 'Empresa',
                'empresa_id' => $clienteId,
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('clientes')->insert([
            [
                'id' => $empresaId,
                'apelido' => 'emp20',
                'area_id' => 2,
                'cnpj' => '22222222000191',
                'nome_fantasia' => 'Empresa 20',
                'razao_social' => 'Empresa 20 LTDA',
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $clienteId,
                'apelido' => 'cli21',
                'area_id' => 7,
                'cnpj' => '33333333000191',
                'nome_fantasia' => 'Cliente 21',
                'razao_social' => 'Cliente 21 LTDA',
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('empresa_clientes')->insert([
            ['empresa_id' => $empresaId, 'cliente_id' => $empresaId],
            ['empresa_id' => $empresaId, 'cliente_id' => $clienteId],
        ]);

        DB::table('cliente_funcionarios')->insert([
            'funcionario_id' => 8,
            'cliente_id' => $clienteId,
        ]);

        $this->actingAs(User::find(8));

        $this->getJson('/g/usuario/autenticado')
            ->assertOk()
            ->assertJsonPath('area_id', 7)
            ->assertJsonPath('apelido', 'emp20');
    }

    public function test_get_usuario_sem_cliente_funcionarios_retorna_area_zero(): void
    {
        $empresaId = 10;

        DB::table('users')->insert([
            [
                'id' => 7,
                'nome' => 'Sem Pivot',
                'login' => 'sem.pivot',
                'tipo' => 'Usuario',
                'empresa_id' => $empresaId,
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // ClientesEmpresa plucka users.id — a empresa precisa existir como User.
            [
                'id' => $empresaId,
                'nome' => 'Acme Empresa',
                'login' => 'acme.empresa',
                'tipo' => 'Empresa',
                'empresa_id' => $empresaId,
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('clientes')->insert([
            'id' => $empresaId,
            'apelido' => 'acme',
            'area_id' => 5,
            'cnpj' => '11111111000191',
            'nome_fantasia' => 'Acme',
            'razao_social' => 'Acme LTDA',
            'ativo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('empresa_clientes')->insert([
            'empresa_id' => $empresaId,
            'cliente_id' => $empresaId,
        ]);

        $this->actingAs(User::find(7));

        $this->getJson('/g/usuario/autenticado')
            ->assertOk()
            ->assertJsonPath('area_id', 0)
            ->assertJsonPath('apelido', 'acme');
    }

    private function garantirTabelas(): void
    {
        foreach ([
            'cliente_funcionarios',
            'empresa_clientes',
            'cliente_filials',
            'empresa_configuracoes',
            'cliente_configs',
            'clientes',
            'users',
        ] as $tabela) {
            Schema::dropIfExists($tabela);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('login')->nullable();
            $table->string('tipo')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->boolean('ativo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('apelido')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('nome_fantasia')->nullable();
            $table->string('razao_social')->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cep')->nullable();
            $table->string('municipio')->nullable();
            $table->string('uf')->nullable();
            $table->string('complemento')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('empresa_clientes', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('cliente_id');
        });

        Schema::create('cliente_funcionarios', function (Blueprint $table) {
            $table->unsignedBigInteger('funcionario_id');
            $table->unsignedBigInteger('cliente_id');
        });

        Schema::create('cliente_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->boolean('envia_whatsapp')->nullable();
            $table->timestamps();
        });

        Schema::create('empresa_configuracoes', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->primary();
            $table->string('tipo_frequencia')->nullable();
            $table->integer('tempo_limite_falta')->nullable();
            $table->integer('tempo_limite_saida')->nullable();
            $table->integer('dia_nova_frequencia')->nullable();
            $table->integer('limite_tolerancia')->nullable();
        });

        Schema::create('cliente_filials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->json('dados')->nullable();
            $table->boolean('ativo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
