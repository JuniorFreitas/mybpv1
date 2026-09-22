<?php

namespace Tests\Feature\Security;

use App\Models\Testemunhal;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Cenários de segurança e regressão para POST /g/testemunhal (TestemunhalController::store).
 *
 * Ref.: relatório de auditoria de segurança — Vuln 4 (XSS armazenado via
 * strip_tags que preserva atributos das tags permitidas). Mesmo padrão de
 * sanitização do OcorrenciaRespostaXssTest, aplicado ao campo `texto`.
 */
class TestemunhalTextoXssTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        $this->garantirTabelas();
        $this->withoutMiddleware();
    }

    public function test_seguranca_payload_xss_no_texto_nao_preserva_atributos_perigosos(): void
    {
        $this->postJson('/g/testemunhal', [
            'nome' => 'Depoente Teste',
            'ativo' => 'true',
            'anexo' => [['id' => 999999, 'chave' => 'inexistente']],
            'texto' => '<p onmouseover="alert(document.cookie)">Ótimo</p><a href="javascript:alert(1)">link</a>',
        ])->assertStatus(201);

        $testemunhal = Testemunhal::latest('id')->first();
        $this->assertStringNotContainsString('onmouseover', $testemunhal->texto);
        $this->assertStringNotContainsString('javascript:', $testemunhal->texto);
    }

    public function test_regressao_formatacao_legitima_do_texto_e_preservada(): void
    {
        $this->postJson('/g/testemunhal', [
            'nome' => 'Depoente Teste',
            'ativo' => 'true',
            'anexo' => [['id' => 999999, 'chave' => 'inexistente']],
            'texto' => '<p>Atendimento <strong>excelente</strong>!</p>',
        ])->assertStatus(201);

        $testemunhal = Testemunhal::latest('id')->first();
        $this->assertStringContainsString('<strong>excelente</strong>', $testemunhal->texto);
        $this->assertStringContainsString('Atendimento', $testemunhal->texto);
    }

    private function garantirTabelas(): void
    {
        Schema::dropIfExists('testemunhals');
        Schema::dropIfExists('arquivos');

        Schema::create('testemunhals', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 255);
            $table->string('subtitulo', 255)->nullable();
            $table->text('texto');
            $table->boolean('ativo');
            $table->timestamps();
        });

        // consultada por Arquivo::whereChave()->whereId() no laço de anexos (sem match = no-op)
        Schema::create('arquivos', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->nullable();
            $table->string('nome')->nullable();
            $table->boolean('temporario')->default(true);
            $table->timestamps();
        });
    }
}
