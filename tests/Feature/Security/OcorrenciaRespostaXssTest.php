<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Cenários de segurança e regressão para POST /g/ocorrencia/nova_mensagem
 * (OcorrenciaController::novaMensagem).
 *
 * Ref.: relatório de auditoria de segurança — Vuln 4 (XSS armazenado via
 * strip_tags que preserva atributos das tags permitidas, ex.: onmouseover, href="javascript:").
 *
 * "test_seguranca_*" descreve o comportamento esperado depois da correção e FALHA
 * contra o código atual. "test_regressao_*" descreve a formatação legítima de hoje
 * (negrito/parágrafo sem atributos) e deve continuar passando.
 */
class OcorrenciaRespostaXssTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        Gate::define('ocorrencia', fn () => true);
        $this->garantirTabelas();
        $this->withoutMiddleware();
        Bus::fake();
    }

    public function test_seguranca_payload_xss_na_resposta_nao_preserva_atributos_perigosos(): void
    {
        [$respondente, $ocorrenciaId] = $this->criarCenario();
        $this->actingAs($respondente);

        $this->postJson('/g/ocorrencia/nova_mensagem', [
            'ocorrencia_id' => $ocorrenciaId,
            'resposta' => '<p onmouseover="alert(document.cookie)">Oi</p><a href="javascript:alert(1)">clique</a>',
        ])->assertStatus(201);

        $resposta = DB::table('ocorrencias_respostas')->latest('id')->first();
        $this->assertStringNotContainsString('onmouseover', $resposta->resposta);
        $this->assertStringNotContainsString('javascript:', $resposta->resposta);
    }

    public function test_regressao_formatacao_legitima_da_resposta_e_preservada(): void
    {
        [$respondente, $ocorrenciaId] = $this->criarCenario();
        $this->actingAs($respondente);

        $this->postJson('/g/ocorrencia/nova_mensagem', [
            'ocorrencia_id' => $ocorrenciaId,
            'resposta' => '<p>Prazo <strong>confirmado</strong> para amanhã.</p>',
        ])->assertStatus(201);

        $resposta = DB::table('ocorrencias_respostas')->latest('id')->first();
        $this->assertStringContainsString('<strong>confirmado</strong>', $resposta->resposta);
        $this->assertStringContainsString('Prazo', $resposta->resposta);
    }

    /**
     * @return array{0: User, 1: int} usuário que vai responder e id da ocorrência
     */
    private function criarCenario(): array
    {
        $criador = $this->criarUsuario(['nome' => 'Criador']);
        $mencionado = $this->criarUsuario(['nome' => 'Mencionado']);
        $respondente = $this->criarUsuario(['nome' => 'Respondente']);

        $ocorrenciaId = DB::table('ocorrencias')->insertGetId([
            'assunto' => 'Assunto de teste',
            'quem_criou' => $criador->id,
            'usuario_id' => $mencionado->id,
            'status' => 'novo',
            'tipo' => 'ocorrencia',
            'empresa_id' => $respondente->empresa_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$respondente, $ocorrenciaId];
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
            'ativo' => true,
            'temp' => false,
            'empresa_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));

        return User::find($id);
    }

    private function garantirTabelas(): void
    {
        Schema::dropIfExists('ocorrencias_respostas');
        Schema::dropIfExists('ocorrencias');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('login')->nullable();
            $table->string('password')->nullable();
            $table->string('tipo');
            $table->boolean('ativo')->default(true);
            $table->boolean('temp')->default(false);
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ocorrencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->unsignedBigInteger('setor_id')->nullable();
            $table->string('assunto', 150);
            $table->unsignedBigInteger('quem_criou');
            $table->unsignedBigInteger('quem_atualizou')->nullable();
            $table->dateTime('datahora_finalizou')->nullable();
            $table->unsignedBigInteger('quem_finalizou')->nullable();
            $table->string('status');
            $table->string('tipo');
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();
        });

        Schema::create('ocorrencias_respostas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ocorrencia_id');
            $table->unsignedBigInteger('user_id');
            $table->text('resposta');
            $table->timestamps();
        });
    }
}
