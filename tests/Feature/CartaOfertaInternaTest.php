<?php

namespace Tests\Feature;

use App\Jobs\Entrevista\JobEnvioDocumento;
use App\Jobs\JobEnvioCartaOferta;
use App\Models\CartaOferta;
use App\Models\CartaOfertaTemplate;
use App\Models\Curriculo;
use App\Models\User;
use App\Models\VagasAbertas;
use App\Services\CartaOferta\CartaOfertaInternaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CartaOfertaInternaTest extends TestCase
{
    private int $curriculoId = 9001;

    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        $this->garantirTabelas();
        $this->withoutMiddleware();
        Gate::before(fn () => true);
        $this->actingAs($this->usuarioRh());
    }

    public function test_cria_carta_interna_mybpin_e_enfileira_email_sem_assinatura(): void
    {
        Queue::fake();
        $this->criarTemplatePublicado(10);
        $this->criarCurriculo(10);
        $vaga = $this->criarVagaAberta(10);

        $carta = app(CartaOfertaInternaService::class)->criar([
            'curriculo_id' => $this->curriculoId,
            'vagas_abertas_id' => $vaga->id,
            'setor' => 'RH',
            'salario' => 'R$ 1.500,00',
            'data_inicio' => '15/04/2026',
        ], 10, 1);

        $this->assertSame(CartaOferta::LOCAL_MYBP, $carta->local);
        $this->assertSame(CartaOferta::STATUS_PENDENTE_ANEXO, $carta->status);
        $this->assertSame('RH', $carta->setor);
        $this->assertSame(1, (int) $carta->user_id);
        $this->assertNotEmpty($carta->token);
        $this->assertNotEmpty($carta->public_id);
        $this->assertSame($carta->token, $carta->public_id);

        Queue::assertPushed(JobEnvioCartaOferta::class);
    }

    public function test_bloqueia_criacao_sem_template_publicado(): void
    {
        Queue::fake();

        CartaOfertaTemplate::withoutGlobalScopes()->create([
            'empresa_id' => 10,
            'titulo' => 'Rascunho',
            'conteudo_html' => '<p>x</p>',
            'status' => CartaOfertaTemplate::STATUS_RASCUNHO,
            'versao' => 1,
        ]);

        $this->criarCurriculo(10);
        $vaga = $this->criarVagaAberta(10);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Publique um template');

        app(CartaOfertaInternaService::class)->criar([
            'curriculo_id' => $this->curriculoId,
            'vagas_abertas_id' => $vaga->id,
        ], 10, 1);
    }

    public function test_endpoint_criar_retorna_201(): void
    {
        Queue::fake();
        $this->criarTemplatePublicado(10);
        $this->criarCurriculo(10);
        $vaga = $this->criarVagaAberta(10);

        $response = $this->postJson('/g/admissao/documentos/carta-oferta/criar', [
            'curriculo_id' => $this->curriculoId,
            'vagas_abertas_id' => $vaga->id,
            'setor' => 'Financeiro',
        ]);

        $response->assertStatus(201)->assertJson(['success' => true]);
        $this->assertDatabaseHas('curriculo_carta_oferta', [
            'curriculo_id' => $this->curriculoId,
            'local' => CartaOferta::LOCAL_MYBP,
            'setor' => 'Financeiro',
        ]);
    }

    public function test_aceite_rh_mybpin_atualiza_status_sem_depender_do_sgi(): void
    {
        Queue::fake();
        $this->criarCurriculo(10);

        $carta = CartaOferta::withoutGlobalScopes()->create([
            'token' => 'token-mybp-1',
            'empresa_id' => 10,
            'curriculo_id' => $this->curriculoId,
            'status' => CartaOferta::STATUS_AGUARDANDO_RH,
            'local' => CartaOferta::LOCAL_MYBP,
            'logs' => [],
        ]);

        $response = $this->putJson('/g/admissao/documentos/carta-oferta/responder', [
            'token' => $carta->token,
            'resposta' => CartaOferta::STATUS_ACEITO_RH,
            'integraMybp' => ['qualquer' => true],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('curriculo_carta_oferta', [
            'id' => $carta->id,
            'status' => CartaOferta::STATUS_ACEITO_RH,
            'local' => CartaOferta::LOCAL_MYBP,
        ]);
        Queue::assertPushed(JobEnvioDocumento::class);
    }

    private function usuarioRh(): User
    {
        $user = User::withoutGlobalScopes()->find(1);
        if ($user) {
            return $user;
        }

        DB::table('users')->insert([
            'id' => 1,
            'nome' => 'RH Teste',
            'empresa_id' => 10,
            'tipo' => 'ADMINISTRADOR',
            'ativo' => true,
            'temp' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::withoutGlobalScopes()->findOrFail(1);
    }

    private function criarTemplatePublicado(int $empresaId): CartaOfertaTemplate
    {
        return CartaOfertaTemplate::withoutGlobalScopes()->create([
            'empresa_id' => $empresaId,
            'titulo' => 'Carta Oferta',
            'conteudo_html' => CartaOfertaTemplate::htmlPadrao(),
            'status' => CartaOfertaTemplate::STATUS_PUBLICADO,
            'versao' => 1,
        ]);
    }

    private function criarCurriculo(int $empresaId): void
    {
        DB::table('users')->updateOrInsert(
            ['id' => $this->curriculoId],
            [
                'nome' => 'Candidato Teste',
                'empresa_id' => $empresaId,
                'tipo' => 'CANDIDATO',
                'ativo' => true,
                'temp' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('curriculos')->updateOrInsert(
            ['id' => $this->curriculoId],
            [
                'empresa_id' => $empresaId,
                'nome' => 'Candidato Teste',
                'cpf' => '111.222.333-44',
                'email' => 'candidato@teste.com',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function criarVagaAberta(int $empresaId): VagasAbertas
    {
        $vagaId = DB::table('vagas')->insertGetId([
            'empresa_id' => $empresaId,
            'nome' => 'Analista',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return VagasAbertas::withoutGlobalScopes()->create([
            'empresa_id' => $empresaId,
            'vaga_id' => $vagaId,
            'titulo' => 'Analista',
        ]);
    }

    private function garantirTabelas(): void
    {
        foreach ([
            'curriculo_telefone',
            'curriculo_carta_oferta',
            'carta_oferta_templates',
            'vaga_projetos',
            'projetos',
            'vagas_abertas',
            'vagas',
            'curriculos',
            'users',
            'clientes',
            'cliente_configs',
            'zap_numeros',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('apelido')->nullable();
            $table->string('razao_social')->nullable();
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj')->nullable();
            $table->timestamps();
        });

        Schema::create('cliente_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->boolean('assinatura_digital_habilitada')->default(false);
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('tipo')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('temp')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('curriculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome')->nullable();
            $table->string('cpf')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('curriculo_telefone', function (Blueprint $table) {
            $table->id();
            $table->string('tipo')->nullable();
            $table->string('pais')->nullable();
            $table->string('numero')->nullable();
            $table->unsignedBigInteger('curriculo_id');
            $table->boolean('principal')->default(false);
        });

        Schema::create('vagas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vagas_abertas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('vaga_id')->nullable();
            $table->string('titulo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('projetos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome')->nullable();
            $table->timestamps();
        });

        Schema::create('vaga_projetos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('projeto_id')->nullable();
            $table->unsignedBigInteger('vaga_aberta_id')->nullable();
            $table->integer('qnt_total')->default(0);
            $table->integer('qnt_preenchida')->default(0);
        });

        Schema::create('carta_oferta_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('titulo', 150);
            $table->longText('conteudo_html');
            $table->string('status', 20)->default('publicado');
            $table->unsignedInteger('versao')->default(1);
            $table->unsignedBigInteger('criado_por')->nullable();
            $table->unsignedBigInteger('atualizado_por')->nullable();
            $table->timestamps();
        });

        Schema::create('curriculo_carta_oferta', function (Blueprint $table) {
            $table->id();
            $table->string('token')->nullable();
            $table->uuid('public_id')->nullable();
            $table->string('acesso_codigo_hash')->nullable();
            $table->timestamp('acesso_codigo_expira_em')->nullable();
            $table->timestamp('acesso_codigo_usado_em')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->unsignedBigInteger('feedback_id')->nullable();
            $table->unsignedBigInteger('vagas_abertas_id')->nullable();
            $table->unsignedBigInteger('vaga_projeto_id')->nullable();
            $table->unsignedInteger('arquivo_id')->nullable();
            $table->string('status')->nullable();
            $table->string('local')->nullable();
            $table->string('setor')->nullable();
            $table->string('salario')->nullable();
            $table->date('data_inicio')->nullable();
            $table->json('logs')->nullable();
            $table->timestamps();
        });

        Schema::create('zap_numeros', function (Blueprint $table) {
            $table->id();
            $table->string('telefone')->nullable();
            $table->boolean('ativo')->default(false);
        });

        DB::table('clientes')->insert([
            'id' => 10,
            'apelido' => 'empresa-teste',
            'razao_social' => 'Empresa Teste LTDA',
            'nome_fantasia' => 'Empresa Teste',
            'cnpj' => '00.000.000/0001-00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('cliente_configs')->insert([
            'cliente_id' => 10,
            'assinatura_digital_habilitada' => false,
        ]);
    }
}
