<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\ClientesController;
use App\Jobs\Auth\JobEnvioCodigoMfaLoginEmail;
use App\Models\User;
use App\Services\Auth\MfaLoginService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class MfaLoginWebTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        Cache::flush();
        Queue::fake();
        $this->garantirTabelas();
    }

    public function testLoginSemMfaAutenticaDireto(): void
    {
        $user = $this->criarUsuario();
        DB::table('cliente_configs')->insert([
            'cliente_id' => 10,
            'envia_whatsapp' => false,
            'mfa_login_habilitado' => false,
            'mfa_login_email' => true,
            'mfa_login_whatsapp' => false,
        ]);

        $response = $this->post('/g/login', [
            'login' => $user->login,
            'password' => 'senha-segura',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function testLoginComMfaNaoAutenticaAteCodigoCorreto(): void
    {
        $user = $this->criarUsuario();
        DB::table('cliente_configs')->insert([
            'cliente_id' => 10,
            'envia_whatsapp' => false,
            'mfa_login_habilitado' => true,
            'mfa_login_email' => true,
            'mfa_login_whatsapp' => false,
        ]);

        $response = $this->post('/g/login', [
            'login' => $user->login,
            'password' => 'senha-segura',
        ]);

        $response->assertRedirect(route('login.mfa.show'));
        $this->assertGuest();
        Queue::assertPushed(JobEnvioCodigoMfaLoginEmail::class);

        $job = Queue::pushed(JobEnvioCodigoMfaLoginEmail::class)[0];
        $prop = (new \ReflectionClass($job))->getProperty('codigo');
        $prop->setAccessible(true);
        $codigo = $prop->getValue($job);

        $this->get(route('login.mfa.show'))
            ->assertOk()
            ->assertSee('data-codigo-segundos=', false)
            ->assertSee('data-reenvio-segundos=', false)
            ->assertSee('id="mfa-codigo-valor"', false)
            ->assertSee('id="mfa-btn-reenviar"', false);

        $this->post(route('login.mfa.verify'), ['codigo' => 'WRONGCOD'])
            ->assertRedirect();
        $this->assertGuest();

        $this->post(route('login.mfa.verify'), ['codigo' => $codigo])
            ->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function testCodigoExpiradoNaoAutentica(): void
    {
        $user = $this->criarUsuario();
        DB::table('cliente_configs')->insert([
            'cliente_id' => 10,
            'envia_whatsapp' => false,
            'mfa_login_habilitado' => true,
            'mfa_login_email' => true,
            'mfa_login_whatsapp' => false,
        ]);

        $this->post('/g/login', [
            'login' => $user->login,
            'password' => 'senha-segura',
        ])->assertRedirect(route('login.mfa.show'));

        Cache::forget('mfa_login:codigo:' . $user->id);

        $this->post(route('login.mfa.verify'), ['codigo' => 'ABCDEFGH'])
            ->assertSessionHasErrors('codigo');
        $this->assertGuest();
    }

    public function testValidacaoConfigMfaExigeCanal(): void
    {
        $controller = app(ClientesController::class);
        $method = new ReflectionMethod(ClientesController::class, 'dadosConfigMfaLogin');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $method->invoke($controller, [
            'mfa_login_habilitado' => true,
            'mfa_login_email' => false,
            'mfa_login_whatsapp' => false,
            'envia_whatsapp' => false,
        ]);
    }

    public function testValidacaoConfigMfaWhatsappExigeEnviaWhatsapp(): void
    {
        $controller = app(ClientesController::class);
        $method = new ReflectionMethod(ClientesController::class, 'dadosConfigMfaLogin');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $method->invoke($controller, [
            'mfa_login_habilitado' => true,
            'mfa_login_email' => false,
            'mfa_login_whatsapp' => true,
            'envia_whatsapp' => false,
        ]);
    }

    private function criarUsuario(array $overrides = []): User
    {
        $id = DB::table('users')->insertGetId(array_merge([
            'nome' => 'Usuario Login MFA',
            'login' => 'login.mfa@example.com',
            'password' => bcrypt('senha-segura'),
            'tipo' => 'admin',
            'ativo' => true,
            'temp' => false,
            'empresa_id' => 10,
            'password_changed_at' => now(),
            'require_password_reset' => false,
            'password_reset_days' => 120,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));

        return User::withoutGlobalScopes()->findOrFail($id);
    }

    private function garantirTabelas(): void
    {
        Schema::dropIfExists('acessos');
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
            $table->timestamp('ultimo_acesso')->nullable();
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
            $table->unsignedBigInteger('user_id');
            $table->boolean('principal')->default(false);
        });

        Schema::create('acessos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('ip')->nullable();
            $table->timestamps();
        });
    }
}
