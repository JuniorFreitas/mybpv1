<?php

namespace Tests\Unit\Services\Aniversariante;

use App\Jobs\JobSendNotificacaoWhatsApp;
use App\Models\TelefoneCurriculo;
use App\Services\Aniversariante\AniversarianteWhatsappMensagensPadrao;
use App\Services\Aniversariante\AniversarianteWhatsappService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AniversarianteWhatsappServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        Schema::dropIfExists('parabens_enviados');
        Schema::dropIfExists('curriculo_telefone');
        Schema::dropIfExists('curriculos');
        Schema::dropIfExists('aniversariante_whatsapp_mensagens');
        Schema::dropIfExists('cliente_configs');

        Schema::create('cliente_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->boolean('envia_whatsapp')->default(false);
            $table->boolean('aniversario_whatsapp')->default(false);
        });

        Schema::create('aniversariante_whatsapp_mensagens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedTinyInteger('dia');
            $table->longText('corpo');
            $table->timestamps();
            $table->unique(['empresa_id', 'dia']);
        });

        Schema::create('curriculos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->date('nascimento')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('curriculo_telefone', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->string('pais')->nullable();
            $table->string('numero')->nullable();
            $table->unsignedBigInteger('curriculo_id');
            $table->boolean('principal')->default(false);
        });

        Schema::create('parabens_enviados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->integer('ano');
            $table->string('status')->nullable();
            $table->string('whatsapp_status', 20)->nullable();
        });
    }

    public function test_catalogo_tem_trinta_mensagens_com_nome_e_assinatura(): void
    {
        $mensagens = AniversarianteWhatsappMensagensPadrao::todas();

        $this->assertCount(30, $mensagens);

        foreach ($mensagens as $dia => $corpo) {
            $this->assertGreaterThanOrEqual(1, $dia);
            $this->assertLessThanOrEqual(30, $dia);
            $this->assertStringContainsString('[Nome]', $corpo);
            $this->assertStringContainsString('Equipe de RH', $corpo);
        }
    }

    public function test_renderizar_substitui_nome_e_equipe_de_rh(): void
    {
        $service = app(AniversarianteWhatsappService::class);
        $texto = $service->renderizar("Olá [Nome]!\n#BPTEAM", 'Maria Souza');

        $this->assertSame("Olá Maria Souza!\nEquipe de RH", $texto);
        $this->assertSame(30, $service->diaDaMensagem(31));
        $this->assertSame(1, $service->diaDaMensagem(0));
    }

    public function test_nao_enfileira_quando_cliente_nao_habilitou(): void
    {
        Queue::fake();
        $this->empresa(77, true, false);
        $this->pessoa(10, '1990-05-05');
        $this->telefone(10, TelefoneCurriculo::TIPO_WHATS, true);

        app(AniversarianteWhatsappService::class)->enviarSeHabilitado((object) [
            'id' => 10,
            'nome' => 'Maria Souza',
            'empresa_id' => 77,
        ]);

        Queue::assertNotPushed(JobSendNotificacaoWhatsApp::class);
    }

    public function test_enfileira_mensagem_do_dia_somente_no_whatsapp_principal(): void
    {
        Queue::fake();
        $this->empresa(77, true, true);
        $this->pessoa(10, '1990-05-05');
        $this->telefone(10, TelefoneCurriculo::TIPO_CELULAR, false);
        $this->telefone(10, TelefoneCurriculo::TIPO_WHATS, false);
        $this->telefone(10, TelefoneCurriculo::TIPO_WHATS, true, '(98) 99999-1010');

        DB::table('aniversariante_whatsapp_mensagens')->insert([
            'empresa_id' => 77,
            'dia' => 5,
            'corpo' => "Parabéns, [Nome]!\n#BPTEAM",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('parabens_enviados')->insert([
            'curriculo_id' => 10,
            'empresa_id' => 77,
            'ano' => (int) now()->format('Y'),
            'status' => 'enviado',
        ]);

        $service = app(AniversarianteWhatsappService::class);
        $service->enviarSeHabilitado((object) [
            'id' => 10,
            'nome' => 'Maria Souza',
            'empresa_id' => 77,
        ]);

        Queue::assertPushed(JobSendNotificacaoWhatsApp::class, function (JobSendNotificacaoWhatsApp $job) {
            return $job->dados['telefone'] === '5598999991010'
                && $job->dados['mensagem'] === "Parabéns, Maria Souza!\nEquipe de RH"
                && $job->dados['_whatsapp_meta']['tipo'] === 'aniversario'
                && $job->delay !== null
                && $job->delay->between(
                    now()->addSeconds(AniversarianteWhatsappService::DELAY_MIN_SEGUNDOS - 1),
                    now()->addSeconds(AniversarianteWhatsappService::DELAY_MAX_SEGUNDOS + 1)
                );
        });

        $this->assertSame(
            AniversarianteWhatsappService::STATUS_ENFILEIRADO,
            DB::table('parabens_enviados')->where('curriculo_id', 10)->value('whatsapp_status')
        );
        $this->assertSame(30, DB::table('aniversariante_whatsapp_mensagens')->where('empresa_id', 77)->count());

        $service->enviarSeHabilitado((object) [
            'id' => 10,
            'nome' => 'Maria Souza',
            'empresa_id' => 77,
        ]);

        Queue::assertPushed(JobSendNotificacaoWhatsApp::class, 1);
    }

    public function test_sem_whatsapp_principal_marca_ignorado(): void
    {
        Queue::fake();
        $this->empresa(77, true, true);
        $this->pessoa(11, '1990-05-05');
        $this->telefone(11, TelefoneCurriculo::TIPO_CELULAR, true);

        app(AniversarianteWhatsappService::class)->enviarSeHabilitado((object) [
            'id' => 11,
            'nome' => 'João',
            'empresa_id' => 77,
        ]);

        Queue::assertNotPushed(JobSendNotificacaoWhatsApp::class);
        $this->assertSame(
            AniversarianteWhatsappService::STATUS_IGNORADO,
            DB::table('parabens_enviados')->where('curriculo_id', 11)->value('whatsapp_status')
        );
    }

    private function empresa(int $id, bool $enviaWhatsapp, bool $aniversario): void
    {
        DB::table('cliente_configs')->insert([
            'cliente_id' => $id,
            'envia_whatsapp' => $enviaWhatsapp,
            'aniversario_whatsapp' => $aniversario,
        ]);
    }

    private function pessoa(int $id, string $nascimento): void
    {
        DB::table('curriculos')->insert([
            'id' => $id,
            'nome' => 'Pessoa',
            'nascimento' => $nascimento,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function telefone(int $curriculoId, string $tipo, bool $principal, string $numero = '(98) 98888-0000'): void
    {
        DB::table('curriculo_telefone')->insert([
            'tipo' => $tipo,
            'pais' => '55',
            'numero' => $numero,
            'curriculo_id' => $curriculoId,
            'principal' => $principal,
        ]);
    }
}
