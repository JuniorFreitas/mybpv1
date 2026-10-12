<?php

namespace Tests\Unit;

use App\Jobs\JobComprovanteAceiteCartaOferta;
use App\Models\Arquivo;
use App\Models\CartaOferta;
use App\Models\CartaOfertaAceite;
use App\Services\CartaOferta\CartaOfertaAceiteService;
use App\Services\CartaOferta\CartaOfertaPdfService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class CartaOfertaAceiteServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false, 'app.url' => 'https://mybp.test']);
        Queue::fake();

        foreach (['cliente_logotipo', 'curriculo_carta_oferta_aceites', 'acessos_publicos_auditorias', 'curriculo_carta_oferta', 'curriculos', 'clientes', 'arquivos'] as $tabela) {
            Schema::dropIfExists($tabela);
        }

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('apelido')->nullable();
            $table->string('nome_fantasia')->nullable();
            $table->string('razao_social')->nullable();
            $table->timestamps();
        });
        Schema::create('curriculos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('cpf')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('arquivos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('file')->nullable();
            $table->string('disco')->nullable();
            $table->timestamps();
        });
        // Usada pelo cabeçalho das telas públicas (logo do cliente).
        Schema::create('cliente_logotipo', function (Blueprint $table) {
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('arquivo_id');
        });
        Schema::create('curriculo_carta_oferta', function (Blueprint $table) {
            $table->id();
            $table->string('token')->nullable();
            $table->uuid('public_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->unsignedBigInteger('arquivo_id')->nullable();
            $table->unsignedBigInteger('proposta_arquivo_id')->nullable();
            $table->string('proposta_hash', 64)->nullable();
            $table->string('status')->nullable();
            $table->string('local')->nullable();
            $table->json('logs')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_12_100000_create_acessos_publicos_auditorias_table.php'))->up();
        (require database_path('migrations/2026_10_12_110000_create_curriculo_carta_oferta_aceites_table.php'))->up();

        DB::table('clientes')->insert(['id' => 10, 'apelido' => 'empresa-teste', 'nome_fantasia' => 'Empresa']);
        DB::table('curriculos')->insert(['id' => 1, 'nome' => 'Candidato', 'cpf' => '123.456.789-09', 'email' => 'candidato@teste.com']);
        DB::table('arquivos')->insert(['id' => 77, 'nome' => 'Carta Oferta.pdf', 'file' => 'carta.pdf', 'disco' => Arquivo::DISCO_DOCUMENTO_ASSINATURA]);

        // A proposta já "congelada": o serviço de PDF não precisa renderizar nada no teste.
        $this->mock(CartaOfertaPdfService::class, function ($mock) {
            $mock->shouldReceive('propostaCongelada')->andReturnUsing(fn () => Arquivo::query()->find(77));
        });
    }

    private function carta(string $status = CartaOferta::STATUS_PENDENTE_ANEXO): CartaOferta
    {
        $publicId = (string) Str::uuid();

        return CartaOferta::withoutGlobalScopes()->create([
            'token' => $publicId,
            'public_id' => $publicId,
            'empresa_id' => 10,
            'curriculo_id' => 1,
            'proposta_arquivo_id' => 77,
            'proposta_hash' => str_repeat('a', 64),
            'status' => $status,
            'local' => CartaOferta::LOCAL_MYBP,
            'logs' => [],
        ]);
    }

    private function request(): Request
    {
        return Request::create('/empresa-teste/carta-oferta/x/aceitar', 'POST', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'PHPUnit Browser',
        ]);
    }

    public function test_aceite_registra_evidencias_mantem_status_e_agenda_comprovante(): void
    {
        $service = app(CartaOfertaAceiteService::class);
        $carta = $this->carta();

        $aceite = $service->aceitar($carta, $this->request());

        $this->assertSame(CartaOfertaAceite::DECISAO_ACEITO, $aceite->decisao);
        $this->assertSame('CANDIDATO', mb_strtoupper($aceite->nome));
        $this->assertSame('127.0.0.1', $aceite->ip);
        $this->assertSame('PHPUnit Browser', $aceite->user_agent);
        $this->assertTrue($aceite->consentimento);
        $this->assertSame(CartaOfertaAceiteService::TEXTO_CONSENTIMENTO, $aceite->consentimento_texto);
        $this->assertSame(str_repeat('a', 64), $aceite->proposta_hash);
        $this->assertSame(64, strlen($aceite->hash_evidencia));
        $this->assertTrue(Str::isUuid($aceite->codigo_verificacao));
        $this->assertTrue($service->integro($aceite->fresh()));

        // Aceitar não encerra: o candidato ainda anexa a carta assinada.
        $carta->refresh();
        $this->assertSame(CartaOferta::STATUS_PENDENTE_ANEXO, $carta->status);
        $this->assertSame('Aceito pelo candidato', $carta->logs[0]['status']);

        $this->assertSame(1, DB::table('acessos_publicos_auditorias')->where('evento', 'carta_aceita')->count());
        Queue::assertPushed(JobComprovanteAceiteCartaOferta::class, fn ($job) => $job->aceiteId === $aceite->id);

        $this->assertSame(
            'https://mybp.test/empresa-teste/carta-oferta/verificacao?c=' . $aceite->codigo_verificacao . '&h=' . $aceite->hash_evidencia,
            $service->urlVerificacao($aceite)
        );
    }

    public function test_recusa_registra_motivo_e_muda_status(): void
    {
        $service = app(CartaOfertaAceiteService::class);
        $carta = $this->carta();

        $aceite = $service->recusar($carta, $this->request(), '  Aceitei outra proposta  ');

        $this->assertSame(CartaOfertaAceite::DECISAO_RECUSADO, $aceite->decisao);
        $this->assertSame('Aceitei outra proposta', $aceite->motivo);
        $this->assertSame(CartaOferta::STATUS_RECUSADO_CANDIDATO, $carta->fresh()->status);
        $this->assertSame(1, DB::table('acessos_publicos_auditorias')->where('evento', 'carta_recusada')->count());
        $this->assertTrue($service->integro($aceite->fresh()));
    }

    public function test_nao_permite_responder_duas_vezes_nem_fora_do_status_pendente(): void
    {
        $service = app(CartaOfertaAceiteService::class);
        $carta = $this->carta();
        $service->aceitar($carta, $this->request());

        try {
            $service->recusar($carta->fresh(), $this->request(), null);
            $this->fail('Não deveria aceitar segunda resposta.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('já foi respondida', $e->getMessage());
        }

        $this->expectException(\RuntimeException::class);
        $service->aceitar($this->carta(CartaOferta::STATUS_EXPIRADO), $this->request());
    }

    public function test_decisao_e_imutavel_e_adulteracao_no_banco_e_detectada(): void
    {
        $service = app(CartaOfertaAceiteService::class);
        $aceite = $service->aceitar($this->carta(), $this->request());

        // Só o comprovante pode ser anexado depois.
        $aceite->update(['comprovante_arquivo_id' => 77]);
        $this->assertSame(77, $aceite->fresh()->comprovante_arquivo_id);

        try {
            $aceite->update(['decisao' => CartaOfertaAceite::DECISAO_RECUSADO]);
            $this->fail('Não deveria permitir alterar a decisão.');
        } catch (\LogicException $e) {
        }

        DB::table('curriculo_carta_oferta_aceites')->where('id', $aceite->id)->update(['ip' => '10.9.9.9']);
        $this->assertFalse($service->integro($aceite->fresh()));
    }

    public function test_http_verificacao_confirma_registro_valido_e_rejeita_hash_errado(): void
    {
        $service = app(CartaOfertaAceiteService::class);
        $aceite = $service->aceitar($this->carta(), $this->request());

        $this->get("/empresa-teste/carta-oferta/verificacao?c={$aceite->codigo_verificacao}&h={$aceite->hash_evidencia}")
            ->assertOk()
            ->assertSee('Registro autêntico')
            ->assertSee('***.456.789-**')
            ->assertDontSee('123.456.789-09');

        $this->get("/empresa-teste/carta-oferta/verificacao?c={$aceite->codigo_verificacao}&h=" . str_repeat('0', 64))
            ->assertOk()
            ->assertSee('Registro não confirmado')
            ->assertDontSee('CANDIDATO');
    }

    public function test_http_aceitar_e_proposta_exigem_sessao_autenticada(): void
    {
        $carta = $this->carta();
        $base = '/empresa-teste/carta-oferta/' . $carta->public_id;

        $this->postJson($base . '/aceitar', ['consentimento' => true])->assertStatus(401);
        $this->postJson($base . '/recusar', [])->assertStatus(401);
        $this->get($base . '/proposta.pdf')->assertStatus(403);
        $this->get($base . '/comprovante.pdf')->assertStatus(403);

        $this->assertSame(0, CartaOfertaAceite::query()->count());
    }

    public function test_http_aceitar_exige_marcar_a_declaracao(): void
    {
        $carta = $this->carta();
        $sessao = ['carta_oferta_auth_' . $carta->public_id => true];
        $base = '/empresa-teste/carta-oferta/' . $carta->public_id;

        $this->withSession($sessao)->postJson($base . '/aceitar', [])->assertStatus(422);
        $this->assertSame(0, CartaOfertaAceite::query()->count());

        $this->withSession($sessao)->postJson($base . '/aceitar', ['consentimento' => true])
            ->assertOk()
            ->assertJsonPath('aceite.decisao', 'aceito')
            ->assertJsonPath('aceite_eletronico', true);
    }
}
