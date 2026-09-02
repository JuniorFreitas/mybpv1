<?php

namespace Tests\Feature;

use App\Http\Controllers\DocumentoPreadmissaoController;
use App\Models\DocumentosCurriculosAdmissaoEmpresa;
use App\Models\DocumentosCurriculosCatAdmissaoEmpresa;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DocumentoPreadmissaoCadastroTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        $this->garantirTabelas();
        $this->registrarGates(true);
        $this->withoutMiddleware();
    }

    public function test_store_grava_documento_da_empresa(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);
        $categoria = $this->criarCategoria();

        $response = $this->postJson('/g/cadastro/documentos-preadmissao', [
            'label' => 'ATESTADO MÉDICO',
            'categoria_id' => $categoria->id,
            'ordem' => 10,
            'ativo' => true,
            'configuracoes' => [
                'obrigatorio' => true,
                'apenas_pdf_img' => true,
            ],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('documentos_curriculos_adm_empresa', [
            'tipo' => 'atestado_medico',
            'metodo' => 'AtestadoMedico',
            'empresa_id' => 10,
            'categoria_id' => $categoria->id,
        ]);
    }

    public function test_index_sem_permissao_base_retorna_403(): void
    {
        $this->registrarGates(false);
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);

        $this->get('/g/cadastro/documentos-preadmissao')->assertStatus(403);
    }

    public function test_atualizar_sem_permissao_base_retorna_403(): void
    {
        $this->registrarGates(false);
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);

        $this->postJson('/g/cadastro/documentos-preadmissao/atualizar', [
            'pages' => 20,
            'page' => 1,
        ])->assertStatus(403);
    }

    public function test_index_informa_permissoes_granulares_para_interface(): void
    {
        Gate::define('cadastro_documentos_preadmissao_insert', fn () => false);
        Gate::define('cadastro_documentos_preadmissao_update', fn () => false);
        $this->actingAs($this->criarUsuarioEmpresa());

        $view = app(DocumentoPreadmissaoController::class)->index();

        $this->assertFalse($view->getData()['canInsert']);
        $this->assertFalse($view->getData()['canUpdate']);
    }

    public function test_store_sem_permissao_insercao_retorna_403(): void
    {
        $this->registrarGates(true);
        Gate::define('cadastro_documentos_preadmissao_insert', fn () => false);
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);

        $response = $this->postJson('/g/cadastro/documentos-preadmissao', [
            'label' => 'DOC',
            'categoria_nova' => 'NOVA',
            'ativo' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_update_e_ativa_desativa_sem_permissao_update_retornam_403(): void
    {
        Gate::define('cadastro_documentos_preadmissao_update', fn () => false);
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);
        $categoria = $this->criarCategoria();
        $doc = $this->criarDocumento(['categoria_id' => $categoria->id]);

        $this->putJson("/g/cadastro/documentos-preadmissao/{$doc->id}", [
            'label' => 'ALTERADO',
            'categoria_id' => $categoria->id,
            'ativo' => true,
        ])->assertStatus(403);

        $this->putJson("/g/cadastro/documentos-preadmissao/{$doc->id}/ativa-desativa")
            ->assertStatus(403);

        $this->assertSame('DOC', $doc->fresh()->label);
        $this->assertTrue($doc->fresh()->ativo);
    }

    public function test_store_sem_empresa_id_retorna_400(): void
    {
        $user = $this->criarUsuarioEmpresa(['empresa_id' => null]);
        $this->actingAs($user);
        $categoria = $this->criarCategoria();

        $this->postJson('/g/cadastro/documentos-preadmissao', [
            'label' => 'DOC SEM EMPRESA',
            'categoria_id' => $categoria->id,
            'ativo' => true,
        ])
            ->assertStatus(400)
            ->assertJson(['msg' => 'Usuário sem empresa vinculada.']);
    }

    public function test_store_recusa_tipo_duplicado(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);
        $categoria = $this->criarCategoria();
        $this->criarDocumento([
            'categoria_id' => $categoria->id,
            'label' => 'ATESTADO MÉDICO',
            'tipo' => 'atestado_medico',
        ]);

        $this->postJson('/g/cadastro/documentos-preadmissao', [
            'label' => 'Atestado Medico',
            'categoria_id' => $categoria->id,
            'ativo' => true,
        ])
            ->assertStatus(400)
            ->assertJson(['msg' => 'Já existe um documento com este nome.']);
    }

    public function test_listagem_nao_expoe_documento_de_outra_empresa(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);
        $this->criarDocumento([
            'empresa_id' => 99,
            'categoria_id' => $this->criarCategoria(['empresa_id' => 99, 'label' => 'OUTRA'])->id,
            'label' => 'SECRETO',
            'tipo' => 'secreto',
        ]);
        $this->criarDocumento([
            'label' => 'MEU DOC',
            'tipo' => 'meu_doc',
            'categoria_id' => $this->criarCategoria()->id,
        ]);

        $response = $this->postJson('/g/cadastro/documentos-preadmissao/atualizar', [
            'pages' => 20,
            'page' => 1,
        ]);

        $response->assertOk();
        $labels = collect($response->json('dados.items'))->pluck('label');
        $this->assertFalse($labels->contains('SECRETO'));
        $this->assertTrue($labels->contains('MEU DOC'));
        $this->assertTrue(collect($response->json('dados.items'))->pluck('tipo')->contains('foto3x4'));
        $this->assertNotEmpty($response->json('dados.categorias'));
    }

    public function test_nao_altera_nem_desativa_foto3x4(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);

        $this->postJson('/g/cadastro/documentos-preadmissao/atualizar', ['pages' => 20, 'page' => 1]);

        $foto = DocumentosCurriculosAdmissaoEmpresa::query()
            ->where('empresa_id', 10)
            ->where('tipo', 'foto3x4')
            ->first();

        $this->assertNotNull($foto);

        $this->putJson("/g/cadastro/documentos-preadmissao/{$foto->id}", [
            'label' => 'FOTO ALTERADA',
            'categoria_id' => $foto->categoria_id,
            'ativo' => true,
        ])->assertStatus(400)->assertJson([
            'msg' => 'A foto 3x4 é um documento padrão do sistema e não pode ser alterada.',
        ]);

        $this->putJson("/g/cadastro/documentos-preadmissao/{$foto->id}/ativa-desativa")
            ->assertStatus(400)
            ->assertJson([
                'msg' => 'A foto 3x4 é um documento padrão do sistema e não pode ser alterada.',
            ]);

        $this->assertTrue($foto->fresh()->ativo);
        $this->assertSame('FOTO 3X4', $foto->fresh()->label);
    }

    public function test_update_preserva_tipo_e_nao_acessa_outra_empresa(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);
        $categoria = $this->criarCategoria();
        $doc = $this->criarDocumento([
            'categoria_id' => $categoria->id,
            'label' => 'RG/CPF',
            'tipo' => 'anexoscpfrg',
            'metodo' => 'AnexosCpfRg',
        ]);

        $response = $this->putJson("/g/cadastro/documentos-preadmissao/{$doc->id}", [
            'label' => 'RG E CPF',
            'tipo' => 'hacked',
            'categoria_id' => $categoria->id,
            'ativo' => true,
            'ordem' => 2,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('documentos_curriculos_adm_empresa', [
            'id' => $doc->id,
            'label' => 'RG E CPF',
            'tipo' => 'anexoscpfrg',
            'metodo' => 'AnexosCpfRg',
        ]);

        $outro = $this->criarDocumento([
            'empresa_id' => 99,
            'categoria_id' => $this->criarCategoria(['empresa_id' => 99, 'label' => 'X'])->id,
            'label' => 'OUTRO',
            'tipo' => 'outro',
        ]);

        $this->putJson("/g/cadastro/documentos-preadmissao/{$outro->id}", [
            'label' => 'HACK',
            'categoria_id' => $categoria->id,
            'ativo' => true,
        ])->assertStatus(404);
    }

    public function test_ativa_desativa_documento_da_empresa(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);
        $doc = $this->criarDocumento([
            'categoria_id' => $this->criarCategoria()->id,
            'ativo' => true,
        ]);

        $response = $this->putJson("/g/cadastro/documentos-preadmissao/{$doc->id}/ativa-desativa");

        $response->assertStatus(201);
        $this->assertFalse($response->json('ativo'));
        $this->assertFalse($doc->fresh()->ativo);
    }

    public function test_ativa_desativa_nao_acessa_outra_empresa(): void
    {
        $user = $this->criarUsuarioEmpresa();
        $this->actingAs($user);
        $outro = $this->criarDocumento([
            'empresa_id' => 99,
            'categoria_id' => $this->criarCategoria(['empresa_id' => 99, 'label' => 'X'])->id,
            'label' => 'OUTRO',
            'tipo' => 'outro',
        ]);

        $this->putJson("/g/cadastro/documentos-preadmissao/{$outro->id}/ativa-desativa")
            ->assertStatus(404);
    }

    private function garantirTabelas(): void
    {
        Schema::dropIfExists('documentos_curriculos_adm_empresa');
        Schema::dropIfExists('documentos_curriculos_cat_adm_empresa');

        Schema::create('documentos_curriculos_cat_adm_empresa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('label');
            $table->boolean('ativo')->default(true);
        });

        Schema::create('documentos_curriculos_adm_empresa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->string('label');
            $table->string('metodo')->nullable();
            $table->text('descricao')->nullable();
            $table->string('tipo');
            $table->string('url_arquivo')->nullable();
            $table->json('configuracoes')->nullable();
            $table->integer('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->unique(['empresa_id', 'tipo'], 'doc_adm_empresa_tipo_unique');
        });
    }

    private function registrarGates(bool $permitir): void
    {
        Gate::define('cadastro_documentos_preadmissao', fn () => $permitir);
        Gate::define('cadastro_documentos_preadmissao_insert', fn () => $permitir);
        Gate::define('cadastro_documentos_preadmissao_update', fn () => $permitir);
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function criarUsuarioEmpresa(array $dados = []): User
    {
        return new User(array_merge([
            'nome' => 'Usuário Teste',
            'tipo' => 'ADMINISTRADOR',
            'ativo' => true,
            'temp' => false,
            'empresa_id' => 10,
        ], $dados));
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function criarCategoria(array $dados = []): DocumentosCurriculosCatAdmissaoEmpresa
    {
        return DocumentosCurriculosCatAdmissaoEmpresa::create(array_merge([
            'empresa_id' => 10,
            'label' => 'DOCUMENTOS PESSOAIS',
            'ativo' => true,
        ], $dados));
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function criarDocumento(array $dados = []): DocumentosCurriculosAdmissaoEmpresa
    {
        return DocumentosCurriculosAdmissaoEmpresa::create(array_merge([
            'empresa_id' => 10,
            'label' => 'DOC',
            'metodo' => 'Doc',
            'tipo' => 'doc',
            'ordem' => 1,
            'ativo' => true,
            'configuracoes' => [
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => false,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => false,
            ],
        ], $dados));
    }
}
