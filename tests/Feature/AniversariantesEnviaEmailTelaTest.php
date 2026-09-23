<?php

namespace Tests\Feature;

use App\Jobs\JobAniversariantes;
use App\Mail\AniversariantesMail;
use App\Models\ParabensEnviado;
use App\Models\User;
use App\Services\Aniversariante\AniversarianteEnvioDiaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AniversariantesEnviaEmailTelaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);
        $this->withoutMiddleware();
        Gate::define('administracao_aniversariantes', fn () => true);

        Schema::dropIfExists('parabens_enviados');
        Schema::dropIfExists('curriculos');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('login')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('require_password_reset')->default(false);
            $table->integer('password_reset_days')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('curriculos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('email')->nullable();
            $table->date('nascimento')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('parabens_enviados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->integer('ano');
            $table->string('status')->nullable();
        });

        Schema::create('aniversariante_mensagem_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->longText('conteudo_html')->nullable();
            $table->timestamps();
        });
    }

    public function test_envia_email_da_tela_enfileira_job_e_marca_enviando(): void
    {
        Queue::fake();

        $user = User::query()->create([
            'nome' => 'RH Teste',
            'login' => 'rh@teste.com',
            'empresa_id' => 50,
            'ativo' => true,
        ]);

        $this->actingAs($user)
            ->postJson('/g/administracao/aniversariantes/enviaEmail', [
                'selecionados' => [101, 102],
            ])
            ->assertOk();

        Queue::assertPushed(JobAniversariantes::class, function (JobAniversariantes $job) {
            return (int) $job->mail['empresa_id'] === 50
                && $job->mail['selecionados'] === [101, 102];
        });

        $this->assertSame(2, DB::table('parabens_enviados')->where('status', ParabensEnviado::STATUS_ENVIANDO)->count());
    }

    public function test_job_manual_envia_mail_normalizado_e_marca_enviado(): void
    {
        Mail::fake();

        DB::table('curriculos')->insert([
            'id' => 101,
            'nome' => 'Fulano',
            'email' => '  Fulano@Email.COM  ',
            'nascimento' => '1990-09-22',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $job = new JobAniversariantes([
            'selecionados' => [101],
            'empresa_id' => 50,
        ]);
        $job->handle(app(AniversarianteEnvioDiaService::class));

        $status = DB::table('parabens_enviados')->where('curriculo_id', 101)->value('status');
        $this->assertSame(ParabensEnviado::STATUS_ENVIADO, $status);

        Mail::assertSent(AniversariantesMail::class, function (AniversariantesMail $mail) {
            return collect($mail->dados)->get('email') === 'fulano@email.com';
        });
    }

    public function test_job_manual_ignora_sistema_mybp_com_espaco(): void
    {
        Mail::fake();

        DB::table('curriculos')->insert([
            'id' => 102,
            'nome' => 'Sem Email',
            'email' => 'sistema@mybp.com. br',
            'nascimento' => '1990-09-22',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $job = new JobAniversariantes([
            'selecionados' => [102],
            'empresa_id' => 50,
        ]);
        $job->handle(app(AniversarianteEnvioDiaService::class));

        Mail::assertNothingSent();
        $this->assertSame(
            ParabensEnviado::STATUS_NAO,
            DB::table('parabens_enviados')->where('curriculo_id', 102)->value('status')
        );
    }
}
