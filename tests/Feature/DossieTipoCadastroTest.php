<?php

namespace Tests\Feature;

use App\Models\DossieTipo;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DossieTipoCadastroTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->garantirTabela();
        $this->registrarGates();
        $this->withoutMiddleware();
    }

    public function test_store_grava_tipo_da_empresa(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);

        $response = $this->postJson('/g/cadastro/dossietipos', [
            'label' => 'ATESTADO MÉDICO',
            'tem_modelo' => false,
            'permite_assinatura' => false,
            'ordem' => 10,
            'ativo' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('dossie_tipos', [
            'tipo' => 'AtestadoMedico',
            'chave' => 'atestado_medico',
            'empresa_id' => 10,
        ]);
    }

    public function test_update_de_global_cria_override_da_empresa(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);

        $global = DossieTipo::create([
            'empresa_id' => null,
            'tipo' => 'DocSelecao',
            'chave' => 'doc_selecao',
            'label' => 'DOCUMENTO DE SELEÇÃO',
            'tem_modelo' => false,
            'permite_assinatura' => false,
            'ordem' => 1,
            'ativo' => true,
        ]);

        $response = $this->putJson("/g/cadastro/dossietipos/{$global->id}", [
            'label' => 'DOC SELEÇÃO CUSTOM',
            'tem_modelo' => false,
            'permite_assinatura' => false,
            'ordem' => 2,
            'ativo' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('dossie_tipos', [
            'id' => $global->id,
            'label' => 'DOCUMENTO DE SELEÇÃO',
            'empresa_id' => null,
        ]);
        $this->assertDatabaseHas('dossie_tipos', [
            'tipo' => 'DocSelecao',
            'label' => 'DOC SELEÇÃO CUSTOM',
            'empresa_id' => 10,
        ]);
    }

    public function test_listagem_nao_expoe_tipo_de_outra_empresa(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);

        DossieTipo::create([
            'empresa_id' => 99,
            'tipo' => 'DocSecreto',
            'chave' => 'doc_secreto',
            'label' => 'SECRETO',
            'tem_modelo' => false,
            'permite_assinatura' => false,
            'ordem' => 1,
            'ativo' => true,
        ]);

        $response = $this->postJson('/g/cadastro/dossietipos/atualizar', [
            'pages' => 20,
            'page' => 1,
        ]);

        $response->assertOk();
        $labels = collect($response->json('dados.items'))->pluck('label');
        $this->assertFalse($labels->contains('SECRETO'));
        $this->assertFalse($response->json('dados.assinatura_digital_habilitada'));
    }

    public function test_store_nao_grava_assinatura_sem_config_da_empresa(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);

        $response = $this->postJson('/g/cadastro/dossietipos', [
            'label' => 'TERMO SEM CONFIG',
            'tipo' => 'TermoSemConfig',
            'chave' => 'termo_sem_config',
            'tem_modelo' => false,
            'permite_assinatura' => true,
            'ordem' => 10,
            'ativo' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('dossie_tipos', [
            'tipo' => 'TermoSemConfig',
            'empresa_id' => 10,
            'permite_assinatura' => false,
        ]);
    }

    public function test_store_recusa_nome_duplicado(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);

        DossieTipo::create([
            'empresa_id' => null,
            'tipo' => 'AtestadoMedico',
            'chave' => 'atestado_medico',
            'label' => 'ATESTADO MÉDICO',
            'tem_modelo' => false,
            'permite_assinatura' => false,
            'ordem' => 1,
            'ativo' => true,
        ]);

        $response = $this->postJson('/g/cadastro/dossietipos', [
            'label' => 'Atestado Médico',
            'tem_modelo' => false,
            'permite_assinatura' => false,
            'ordem' => 10,
            'ativo' => true,
        ]);

        $response->assertStatus(400);
        $response->assertJson(['msg' => 'Já existe um tipo de dossiê com este nome.']);
    }

    private function garantirTabela(): void
    {
        if (Schema::hasTable('dossie_tipos')) {
            if (!Schema::hasColumn('dossie_tipos', 'modelo_arquivo_id')) {
                Schema::table('dossie_tipos', function (Blueprint $table) {
                    $table->unsignedBigInteger('modelo_arquivo_id')->nullable();
                });
            }
            DossieTipo::query()->delete();

            return;
        }

        Schema::create('dossie_tipos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('tipo', 100);
            $table->string('chave', 100);
            $table->string('label', 255);
            $table->string('tipo_modelo', 100)->nullable();
            $table->string('tipo_documento', 100)->nullable();
            $table->boolean('tem_modelo')->default(false);
            $table->boolean('permite_assinatura')->default(false);
            $table->unsignedBigInteger('modelo_arquivo_id')->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    private function registrarGates(): void
    {
        Gate::define('cadastro_dossie_tipos', fn () => true);
        Gate::define('cadastro_dossie_tipos_insert', fn () => true);
        Gate::define('cadastro_dossie_tipos_update', fn () => true);
    }

    private function criarUsuarioEmpresa(): User
    {
        return new User([
            'nome' => 'Usuário Teste',
            'tipo' => 'ADMINISTRADOR',
            'ativo' => true,
            'temp' => false,
            'empresa_id' => 10,
        ]);
    }
}
