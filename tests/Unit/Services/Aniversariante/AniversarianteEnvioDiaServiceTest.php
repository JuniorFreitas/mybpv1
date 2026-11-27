<?php

namespace Tests\Unit\Services\Aniversariante;

use App\Models\ParabensEnviado;
use App\Services\Aniversariante\AniversarianteEnvioDiaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AniversarianteEnvioDiaServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('parabens_enviados');
        Schema::create('parabens_enviados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->integer('ano');
            $table->string('status')->nullable();
        });
    }

    public function test_email_valido_rejeita_vazio_e_invalido(): void
    {
        $service = new AniversarianteEnvioDiaService();

        $this->assertFalse($service->emailValido(''));
        $this->assertFalse($service->emailValido('   '));
        $this->assertFalse($service->emailValido('sem-arroba'));
        $this->assertTrue($service->emailValido('pessoa@empresa.com'));
        $this->assertTrue($service->emailValido('  Pessoa@Empresa.COM  '));
    }

    public function test_normalizar_email_trim_minusculo_e_sem_espacos(): void
    {
        $service = new AniversarianteEnvioDiaService();

        $this->assertSame('pessoa@empresa.com', $service->normalizarEmail('  Pessoa@Empresa.COM  '));
        $this->assertSame('sistema@mybp.com.br', $service->normalizarEmail('sistema@mybp.com. br'));
    }

    public function test_email_invalido_marca_erro_sem_enviar_mail(): void
    {
        Mail::fake();

        $service = new AniversarianteEnvioDiaService();
        $resultado = $service->processarAniversariante((object) [
            'id' => 10,
            'nome' => 'Teste',
            'email' => '',
            'empresa_id' => 99,
        ], 2026);

        $this->assertSame('erros', $resultado);
        Mail::assertNothingSent();

        $registro = DB::table('parabens_enviados')->where('curriculo_id', 10)->first();
        $this->assertNotNull($registro);
        $this->assertSame(ParabensEnviado::STATUS_ERRO, $registro->status);
    }

    public function test_sistema_mybp_e_ignorado_mesmo_com_espaco_e_maiusculas(): void
    {
        Mail::fake();

        $service = new AniversarianteEnvioDiaService();
        $resultado = $service->processarAniversariante((object) [
            'id' => 11,
            'nome' => 'Sistema',
            'email' => 'Sistema@MyBP.com. br',
            'empresa_id' => 99,
        ], 2026);

        $this->assertSame('ignorados', $resultado);
        Mail::assertNothingSent();

        $registro = DB::table('parabens_enviados')->where('curriculo_id', 11)->first();
        $this->assertSame(ParabensEnviado::STATUS_NAO, $registro->status);
    }

    public function test_marcar_status_atualiza_registro_existente(): void
    {
        $service = new AniversarianteEnvioDiaService();

        $service->marcarStatus(20, 1, 2026, ParabensEnviado::STATUS_ENVIANDO);
        $service->marcarStatus(20, 1, 2026, ParabensEnviado::STATUS_ENVIADO);

        $this->assertSame(1, DB::table('parabens_enviados')->where('curriculo_id', 20)->count());
        $this->assertSame(
            ParabensEnviado::STATUS_ENVIADO,
            DB::table('parabens_enviados')->where('curriculo_id', 20)->value('status')
        );
    }
}
