<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OcorrenciaListaSetoresTagsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        $this->garantirTabelas();
        Gate::define('ocorrencia', fn () => true);
        $this->withoutMiddleware();
    }

    public function test_lista_setores_tags_retorna_apenas_da_empresa_autenticada(): void
    {
        DB::table('ocorrencias_setores')->insert([
            ['nome' => 'RH', 'empresa_id' => 10],
            ['nome' => 'FINANCEIRO OUTRA', 'empresa_id' => 99],
        ]);
        DB::table('tags')->insert([
            ['nome' => 'AJUSTE DP', 'empresa_id' => 10],
            ['nome' => 'TAG OUTRA', 'empresa_id' => 99],
        ]);

        $user = $this->criarUsuarioEmpresa(['empresa_id' => 10]);
        $this->actingAs($user);

        $response = $this->getJson('/g/ocorrencia/listaSetoresTags');

        $response->assertOk()
            ->assertJsonCount(1, 'setores')
            ->assertJsonCount(1, 'tags')
            ->assertJsonPath('setores.0.nome', 'RH')
            ->assertJsonPath('tags.0.nome', 'AJUSTE DP');
    }

    public function test_lista_setores_tags_retorna_arrays_vazios_quando_empresa_sem_cadastro(): void
    {
        $user = $this->criarUsuarioEmpresa(['empresa_id' => 55]);
        $this->actingAs($user);

        $response = $this->getJson('/g/ocorrencia/listaSetoresTags');

        $response->assertOk()
            ->assertExactJson([
                'setores' => [],
                'tags' => [],
            ]);
    }

    public function test_cadastro_tag_bloqueia_duplicidade_na_mesma_empresa(): void
    {
        DB::table('tags')->insert([
            'nome' => 'Ajuste DP',
            'empresa_id' => 10,
        ]);

        $this->actingAs($this->criarUsuarioEmpresa(['empresa_id' => 10]));

        $this->postJson('/g/ocorrencia/cadastro-tag', ['nome' => 'ajuste dp'])
            ->assertStatus(400)
            ->assertJsonPath('erros.nome.0', 'Já existe uma tag com este nome para esta empresa.');
    }

    public function test_cadastro_setor_permite_mesmo_nome_em_outra_empresa(): void
    {
        DB::table('ocorrencias_setores')->insert([
            'nome' => 'RH',
            'empresa_id' => 99,
        ]);

        $this->actingAs($this->criarUsuarioEmpresa(['empresa_id' => 10]));

        $this->postJson('/g/ocorrencia/cadastro-setor', ['nome' => 'RH'])
            ->assertStatus(201);

        $this->assertDatabaseHas('ocorrencias_setores', [
            'nome' => 'RH',
            'empresa_id' => 10,
        ]);
    }

    public function test_cadastro_setor_bloqueia_duplicidade_case_insensitive(): void
    {
        DB::table('ocorrencias_setores')->insert([
            'nome' => 'Financeiro',
            'empresa_id' => 10,
        ]);

        $this->actingAs($this->criarUsuarioEmpresa(['empresa_id' => 10]));

        $this->postJson('/g/ocorrencia/cadastro-setor', ['nome' => '  FINANCEIRO  '])
            ->assertStatus(400)
            ->assertJsonPath('erros.nome.0', 'Já existe um setor com este nome para esta empresa.');
    }

    private function garantirTabelas(): void
    {
        Schema::dropIfExists('ocorrencias_setores');
        Schema::dropIfExists('tags');

        Schema::create('ocorrencias_setores', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->unsignedBigInteger('empresa_id')->nullable();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->unsignedBigInteger('empresa_id')->nullable();
        });
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function criarUsuarioEmpresa(array $dados = []): User
    {
        return new User(array_merge([
            'id' => 1,
            'nome' => 'Usuário Teste',
            'tipo' => 'ADMINISTRADOR',
            'ativo' => true,
            'temp' => false,
            'empresa_id' => 10,
        ], $dados));
    }
}
