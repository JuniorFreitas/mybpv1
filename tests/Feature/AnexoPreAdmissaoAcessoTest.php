<?php

namespace Tests\Feature;

use App\Models\Arquivo;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnexoPreAdmissaoAcessoTest extends TestCase
{
    private const SESSAO_CANDIDATO = ['acesso_publico_anexos' => ['curriculos' => [1], 'arquivos' => []]];

    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        Storage::fake(Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO);

        foreach (['arquivos', 'documentos_curriculos', 'curriculo_carta_oferta', 'feedback_curriculos', 'users'] as $tabela) {
            Schema::dropIfExists($tabela);
        }

        Schema::create('arquivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quem_enviou')->nullable();
            $table->string('nome')->nullable();
            $table->boolean('imagem')->default(false);
            $table->string('layout')->nullable();
            $table->string('extensao')->nullable();
            $table->string('file')->nullable();
            $table->string('thumb')->nullable();
            $table->unsignedBigInteger('bytes')->default(0);
            $table->boolean('temporario')->default(true);
            $table->string('chave')->nullable();
            $table->string('disco')->nullable();
            $table->timestamps();
        });

        Schema::create('documentos_curriculos', function (Blueprint $table) {
            $table->unsignedBigInteger('curriculo_id');
            $table->unsignedBigInteger('arquivo_id');
            $table->string('tipo')->nullable();
        });

        Schema::create('curriculo_carta_oferta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->unsignedBigInteger('arquivo_id')->nullable();
        });

        Schema::create('feedback_curriculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id');
            $table->unsignedBigInteger('empresa_id');
            $table->softDeletes();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->softDeletes();
        });
    }

    private function assinada(string $acao, string $arquivo): string
    {
        return Arquivo::urlAssinadaPreAdmissao($acao, $arquivo);
    }

    private function usuario(int $id, int $empresaId): User
    {
        $usuario = new User();
        $usuario->id = $id;
        $usuario->empresa_id = $empresaId;

        return $usuario;
    }

    private function criarArquivo(int $id, string $file, ?int $curriculoId = null): void
    {
        DB::table('arquivos')->insert([
            'id' => $id,
            'nome' => 'doc',
            'file' => $file,
            'extensao' => 'pdf',
            'temporario' => $curriculoId === null,
            'disco' => Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO,
        ]);
        Storage::disk(Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO)->put($file, '%PDF-1.4 teste');

        if ($curriculoId) {
            DB::table('documentos_curriculos')->insert(['curriculo_id' => $curriculoId, 'arquivo_id' => $id, 'tipo' => 'rg']);
        }
    }

    public function test_sem_login_nem_codigo_nao_envia_nao_ve_nao_baixa_nem_exclui(): void
    {
        $this->criarArquivo(1, 'meu.pdf', 1);

        $this->post('/documentos/uploadAnexos', ['arquivo' => UploadedFile::fake()->create('rg.pdf', 10, 'application/pdf')])
            ->assertStatus(401);
        $this->get($this->assinada('show', 'meu.pdf'))->assertStatus(403);
        $this->get($this->assinada('download', 'meu.pdf'))->assertStatus(403);
        $this->delete($this->assinada('delete', 'meu.pdf'))->assertStatus(403);

        $this->assertSame(1, DB::table('arquivos')->count());
    }

    public function test_candidato_autenticado_acessa_so_os_proprios_arquivos(): void
    {
        $this->criarArquivo(1, 'meu.pdf', 1);
        $this->criarArquivo(2, 'de-outro.pdf', 2);
        $this->criarArquivo(3, 'temporario-de-outro.pdf');

        $this->withSession(self::SESSAO_CANDIDATO)->get($this->assinada('show', 'meu.pdf'))->assertOk();
        $this->withSession(self::SESSAO_CANDIDATO)->get($this->assinada('show', 'de-outro.pdf'))->assertStatus(403);
        $this->withSession(self::SESSAO_CANDIDATO)->get($this->assinada('download', 'de-outro.pdf'))->assertStatus(403);
        $this->withSession(self::SESSAO_CANDIDATO)->delete($this->assinada('delete', 'temporario-de-outro.pdf'))->assertStatus(403);

        $this->assertSame(3, DB::table('arquivos')->count());
    }

    public function test_candidato_acessa_a_carta_oferta_que_ele_mesmo_anexou(): void
    {
        $this->criarArquivo(5, 'carta.pdf');
        DB::table('curriculo_carta_oferta')->insert(['id' => 1, 'curriculo_id' => 1, 'arquivo_id' => 5]);

        $this->withSession(self::SESSAO_CANDIDATO)->get($this->assinada('show', 'carta.pdf'))->assertOk();
        $this->withSession(['acesso_publico_anexos' => ['curriculos' => [2], 'arquivos' => []]])
            ->get($this->assinada('show', 'carta.pdf'))
            ->assertStatus(403);
    }

    public function test_candidato_acessa_o_arquivo_que_acabou_de_enviar_nesta_sessao(): void
    {
        $this->criarArquivo(7, 'recem-enviado.pdf');
        $sessao = ['acesso_publico_anexos' => ['curriculos' => [1], 'arquivos' => [7]]];

        $this->withSession($sessao)->get($this->assinada('show', 'recem-enviado.pdf'))->assertOk();

        // A exclusão em si depende de outras tabelas do sistema; aqui importa só a regra de acesso.
        $request = \Illuminate\Http\Request::create('/documentos/anexo/recem-enviado.pdf', 'DELETE');
        $request->setLaravelSession(app('session.store'));
        $request->session()->put($sessao);
        $regras = app(\App\Services\Preadmissao\AnexoPreAdmissaoAcessoService::class);

        $this->assertTrue($regras->podeAcessar($request, 'recem-enviado.pdf'));

        $request->session()->put('acesso_publico_anexos.arquivos', []);
        $this->assertFalse($regras->podeAcessar($request, 'recem-enviado.pdf'));
    }

    public function test_url_sem_assinatura_ou_expirada_e_recusada_mesmo_para_quem_tem_acesso(): void
    {
        $this->criarArquivo(1, 'meu.pdf', 1);

        $this->withSession(self::SESSAO_CANDIDATO)->get('/documentos/anexo/meu.pdf')->assertStatus(403);
        $this->withSession(self::SESSAO_CANDIDATO)->get('/documentos/anexoDownload/meu.pdf')->assertStatus(403);

        $url = $this->assinada('show', 'meu.pdf');
        $this->travel(Arquivo::CLOUD_URL_ASSINADA_MINUTOS + 1)->minutes();
        $this->withSession(self::SESSAO_CANDIDATO)->get($url)->assertStatus(403);
    }

    public function test_usuario_do_rh_so_acessa_anexo_de_candidato_da_propria_empresa(): void
    {
        $this->criarArquivo(1, 'meu.pdf', 1);
        DB::table('feedback_curriculos')->insert(['id' => 1, 'curriculo_id' => 1, 'empresa_id' => 10]);

        $this->actingAs($this->usuario(50, 10))->get($this->assinada('show', 'meu.pdf'))->assertOk();
        $this->actingAs($this->usuario(50, 10))->get($this->assinada('download', 'meu.pdf'))->assertOk();

        // Outra empresa, mesmo com URL assinada válida.
        $this->actingAs($this->usuario(60, 20))->get($this->assinada('show', 'meu.pdf'))->assertStatus(403);
        $this->actingAs($this->usuario(60, 20))->get($this->assinada('download', 'meu.pdf'))->assertStatus(403);
        $this->actingAs($this->usuario(60, 20))->delete($this->assinada('delete', 'meu.pdf'))->assertStatus(403);
    }

    public function test_usuario_do_rh_acessa_carta_oferta_e_cadastro_da_propria_empresa(): void
    {
        $this->criarArquivo(5, 'carta.pdf');
        DB::table('curriculo_carta_oferta')->insert(['id' => 1, 'empresa_id' => 10, 'curriculo_id' => 1, 'arquivo_id' => 5]);
        $this->criarArquivo(6, 'doc-do-cadastro.pdf', 2);
        DB::table('users')->insert(['id' => 2, 'empresa_id' => 10]);

        $this->actingAs($this->usuario(50, 10))->get($this->assinada('show', 'carta.pdf'))->assertOk();
        $this->actingAs($this->usuario(50, 10))->get($this->assinada('show', 'doc-do-cadastro.pdf'))->assertOk();
        $this->actingAs($this->usuario(60, 20))->get($this->assinada('show', 'carta.pdf'))->assertStatus(403);
        $this->actingAs($this->usuario(60, 20))->get($this->assinada('show', 'doc-do-cadastro.pdf'))->assertStatus(403);
    }

    public function test_arquivo_ainda_nao_vinculado_so_e_visto_por_quem_enviou(): void
    {
        $this->criarArquivo(9, 'rascunho.pdf');
        DB::table('arquivos')->where('id', 9)->update(['quem_enviou' => 50]);

        $this->actingAs($this->usuario(50, 10))->get($this->assinada('show', 'rascunho.pdf'))->assertOk();
        $this->actingAs($this->usuario(51, 10))->get($this->assinada('show', 'rascunho.pdf'))->assertStatus(403);
    }

    public function test_urls_do_model_ja_saem_assinadas_e_o_disco_e_privado(): void
    {
        $arquivo = new Arquivo(['file' => 'meu.pdf', 'disco' => Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO]);

        foreach (['url', 'urlThumb', 'urlDownload', 'urlDelete'] as $atributo) {
            $this->assertStringContainsString('signature=', $arquivo->{$atributo}, $atributo);
            $this->assertStringStartsWith('/documentos/', $arquivo->{$atributo}, $atributo);
        }

        $this->assertSame('private', config('filesystems.disks.' . Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO . '.visibility'));
    }
}
