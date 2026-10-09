<?php

namespace Tests\Unit\Services\Auth;

use App\Jobs\Auth\JobEnvioCodigoMfaLoginEmail;
use App\Jobs\JobSendNotificacaoWhatsApp;
use App\Models\ClienteConfig;
use App\Models\User;
use App\Services\Auth\MfaLoginService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class MfaLoginServiceTest extends TestCase
{
    private MfaLoginService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        Cache::flush();
        Queue::fake();

        Schema::dropIfExists('usuarios_telefone');
        Schema::dropIfExists('cliente_configs');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('login')->nullable();
            $table->string('password')->nullable();
            $table->string('tipo')->default('user');
            $table->boolean('ativo')->default(true);
            $table->boolean('temp')->default(false);
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->boolean('require_password_reset')->default(false);
            $table->integer('password_reset_days')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cliente_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->boolean('envia_whatsapp')->default(false);
            $table->boolean('mfa_login_habilitado')->default(false);
            $table->boolean('mfa_login_email')->default(true);
            $table->boolean('mfa_login_whatsapp')->default(false);
        });

        Schema::create('usuarios_telefone', function (Blueprint $table) {
            $table->id();
            $table->string('tipo')->nullable();
            $table->string('pais')->nullable();
            $table->string('numero')->nullable();
            $table->string('ramal')->nullable();
            $table->string('detalhe')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->boolean('principal')->default(false);
        });

        $this->service = app(MfaLoginService::class);
    }

    public function testEmpresaSemMfaNaoExige(): void
    {
        $user = $this->criarUsuario();
        DB::table('cliente_configs')->insert([
            'cliente_id' => 10,
            'envia_whatsapp' => false,
            'mfa_login_habilitado' => false,
            'mfa_login_email' => true,
            'mfa_login_whatsapp' => false,
        ]);

        $this->assertFalse($this->service->empresaExigeMfa($user));
    }

    public function testIniciaChallengeEnviaEmailEGuardaHash(): void
    {
        $user = $this->criarUsuario();
        $this->habilitarMfa(email: true, whatsapp: false);

        $request = Request::create('/g/login', 'POST');
        $session = app('session')->driver();
        $session->start();
        $request->setLaravelSession($session);

        $resultado = $this->service->iniciarChallenge($user, $request);

        $this->assertContains('email', $resultado['canais']);
        $this->assertArrayHasKey('email', $resultado['destinos_mascarados']);
        $this->assertNotNull(Cache::get('mfa_login:codigo:' . $user->id));
        Queue::assertPushed(JobEnvioCodigoMfaLoginEmail::class);
        Queue::assertNotPushed(JobSendNotificacaoWhatsApp::class);
        $this->assertSame($user->id, $session->get(MfaLoginService::SESSION_PENDING_KEY)['user_id']);
    }

    public function testIniciaChallengeWhatsappQuandoCanalETelefoneOk(): void
    {
        $user = $this->criarUsuario();
        $this->habilitarMfa(email: false, whatsapp: true, enviaWhatsapp: true);
        DB::table('usuarios_telefone')->insert([
            'tipo' => 'whatsapp',
            'pais' => '55',
            'numero' => '98999023762',
            'user_id' => $user->id,
            'principal' => true,
        ]);

        $request = Request::create('/g/login', 'POST');
        $session = app('session')->driver();
        $session->start();
        $request->setLaravelSession($session);

        $resultado = $this->service->iniciarChallenge($user, $request);

        $this->assertContains('whatsapp', $resultado['canais']);
        Queue::assertPushed(JobSendNotificacaoWhatsApp::class);
        Queue::assertNotPushed(JobEnvioCodigoMfaLoginEmail::class);
    }

    public function testFalhaQuandoNenhumCanalDisponivel(): void
    {
        $user = $this->criarUsuario(['login' => 'nao-e-email']);
        $this->habilitarMfa(email: true, whatsapp: true, enviaWhatsapp: true);

        $request = Request::create('/g/login', 'POST');
        $session = app('session')->driver();
        $session->start();
        $request->setLaravelSession($session);

        $this->expectException(RuntimeException::class);
        $this->service->iniciarChallenge($user, $request);
    }

    public function testValidaCodigoCorretoERejeitaInvalido(): void
    {
        $user = $this->criarUsuario();
        $this->habilitarMfa(email: true, whatsapp: false);

        $request = Request::create('/g/login', 'POST');
        $session = app('session')->driver();
        $session->start();
        $request->setLaravelSession($session);

        $this->service->iniciarChallenge($user, $request);

        $job = Queue::pushed(JobEnvioCodigoMfaLoginEmail::class)[0];
        $codigo = (new \ReflectionClass($job))->getProperty('codigo');
        $codigo->setAccessible(true);
        $valor = $codigo->getValue($job);

        $this->assertTrue($this->service->validarCodigo($user, $valor));
        $this->assertFalse($this->service->validarCodigo($user, 'XXXXXXXX'));
        $this->assertGreaterThan(0, $this->service->segundosRestantesCodigo($user));
        $registro = Cache::get('mfa_login:codigo:' . $user->id);
        $this->assertArrayHasKey('expira_em', $registro);
    }

    public function testBloqueiaAposMaximoDeTentativas(): void
    {
        $user = $this->criarUsuario();

        for ($i = 0; $i < 5; $i++) {
            $status = $this->service->registrarFalha($user);
        }

        $this->assertTrue($status['bloqueado']);
        $this->assertSame(0, $status['tentativas_restantes']);
    }

    public function testReenvioRespeitaCooldown(): void
    {
        $user = $this->criarUsuario();
        $this->habilitarMfa(email: true, whatsapp: false);

        $request = Request::create('/g/login', 'POST');
        $session = app('session')->driver();
        $session->start();
        $request->setLaravelSession($session);

        $this->service->iniciarChallenge($user, $request);
        $resultado = $this->service->reenviarCodigo($user);

        $this->assertFalse($resultado['enviado']);
        $this->assertGreaterThan(0, $resultado['segundos_restantes']);
    }

    private function criarUsuario(array $overrides = []): User
    {
        $id = DB::table('users')->insertGetId(array_merge([
            'nome' => 'Usuario MFA',
            'login' => 'usuario.mfa@example.com',
            'password' => bcrypt('senha-segura'),
            'tipo' => 'admin',
            'ativo' => true,
            'temp' => false,
            'empresa_id' => 10,
            'password_changed_at' => now(),
            'require_password_reset' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));

        return User::withoutGlobalScopes()->findOrFail($id);
    }

    private function habilitarMfa(bool $email, bool $whatsapp, bool $enviaWhatsapp = false): void
    {
        ClienteConfig::query()->create([
            'cliente_id' => 10,
            'envia_whatsapp' => $enviaWhatsapp,
            'mfa_login_habilitado' => true,
            'mfa_login_email' => $email,
            'mfa_login_whatsapp' => $whatsapp,
        ]);
    }
}
