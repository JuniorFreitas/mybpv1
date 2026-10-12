<?php

namespace Tests\Unit;

use App\Jobs\JobEnvioCodigoAcessoCartaOferta;
use App\Models\CartaOferta;
use App\Models\Curriculo;
use App\Services\CartaOferta\CartaOfertaAcessoPublicoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CartaOfertaAcessoPublicoServiceTest extends TestCase
{
    private int $curriculoId = 7001;

    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);

        Schema::dropIfExists('curriculo_carta_oferta_aceites');
        Schema::dropIfExists('curriculo_carta_oferta');
        Schema::dropIfExists('curriculos');
        Schema::dropIfExists('users');
        Schema::dropIfExists('clientes');

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('apelido')->nullable();
            $table->string('nome_fantasia')->nullable();
            $table->string('razao_social')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('curriculos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('cpf')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('curriculo_carta_oferta', function (Blueprint $table) {
            $table->id();
            $table->string('token')->nullable();
            $table->uuid('public_id')->nullable();
            $table->string('acesso_codigo_hash')->nullable();
            $table->timestamp('acesso_codigo_expira_em')->nullable();
            $table->timestamp('acesso_codigo_usado_em')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->string('status')->nullable();
            $table->string('local')->nullable();
            $table->json('logs')->nullable();
            $table->timestamps();
        });

        (require database_path('migrations/2026_10_12_110000_create_curriculo_carta_oferta_aceites_table.php'))->up();

        DB::table('clientes')->insert([
            'id' => 10,
            'apelido' => 'empresa-teste',
            'nome_fantasia' => 'Empresa',
            'razao_social' => 'Empresa LTDA',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id' => $this->curriculoId,
            'nome' => 'Candidato',
            'empresa_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('curriculos')->insert([
            'id' => $this->curriculoId,
            'nome' => 'Candidato',
            'cpf' => '123.456.789-09',
            'email' => 'cand@teste.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_solicitar_e_validar_codigo_uso_unico(): void
    {
        Queue::fake();
        $service = app(CartaOfertaAcessoPublicoService::class);

        $carta = CartaOferta::withoutGlobalScopes()->create([
            'token' => 'pub-1',
            'public_id' => 'pub-1',
            'empresa_id' => 10,
            'curriculo_id' => $this->curriculoId,
            'status' => CartaOferta::STATUS_PENDENTE_ANEXO,
            'local' => CartaOferta::LOCAL_MYBP,
            'logs' => [],
        ]);

        $envio = $service->solicitarCodigo($carta->fresh(), '12345678909');
        $this->assertSame('c***@teste.com', $envio['email_mascarado']);
        Queue::assertPushed(JobEnvioCodigoAcessoCartaOferta::class);

        $job = null;
        Queue::assertPushed(JobEnvioCodigoAcessoCartaOferta::class, function ($j) use (&$job) {
            $job = $j;
            return true;
        });
        $codigo = $job->mail['codigo'];
        $this->assertSame(8, strlen($codigo));

        $payload = $service->validarCodigo($carta->fresh(), $codigo);
        $this->assertTrue($payload['autenticado']);
        $this->assertSame('CANDIDATO', mb_strtoupper($payload['candidato']['nome']));
        $this->assertArrayNotHasKey('cpf', $payload['candidato']);

        $this->expectException(\RuntimeException::class);
        $service->validarCodigo($carta->fresh(), $codigo);
    }

    public function test_cpf_invalido_bloqueia(): void
    {
        $service = app(CartaOfertaAcessoPublicoService::class);
        $carta = CartaOferta::withoutGlobalScopes()->create([
            'token' => 'pub-2',
            'public_id' => 'pub-2',
            'empresa_id' => 10,
            'curriculo_id' => $this->curriculoId,
            'status' => CartaOferta::STATUS_PENDENTE_ANEXO,
            'local' => CartaOferta::LOCAL_MYBP,
            'logs' => [],
        ]);

        $this->expectException(\RuntimeException::class);
        $service->solicitarCodigo($carta, '00000000000');
    }

    private function criarCarta(string $publicId): CartaOferta
    {
        return CartaOferta::withoutGlobalScopes()->create([
            'token' => $publicId,
            'public_id' => $publicId,
            'empresa_id' => 10,
            'curriculo_id' => $this->curriculoId,
            'status' => CartaOferta::STATUS_PENDENTE_ANEXO,
            'local' => CartaOferta::LOCAL_MYBP,
            'logs' => [],
        ]);
    }

    public function test_reenvio_respeita_cooldown(): void
    {
        $this->freezeSecond();
        Queue::fake();
        $service = app(CartaOfertaAcessoPublicoService::class);
        $carta = $this->criarCarta('pub-3');

        $envio = $service->solicitarCodigo($carta, '12345678909');
        $this->assertSame(CartaOfertaAcessoPublicoService::CODIGO_TTL_MINUTOS * 60, $envio['acesso']['codigo_segundos']);
        $this->assertSame(
            CartaOfertaAcessoPublicoService::CODIGO_REENVIO_COOLDOWN_SEGUNDOS,
            $envio['acesso']['reenvio_segundos']
        );

        try {
            $service->reenviarCodigo($carta);
            $this->fail('Reenvio deveria respeitar o cooldown.');
        } catch (\RuntimeException $e) {
            Queue::assertPushed(JobEnvioCodigoAcessoCartaOferta::class, 1);
        }

        $this->travel(CartaOfertaAcessoPublicoService::CODIGO_REENVIO_COOLDOWN_SEGUNDOS + 1)->seconds();
        $service->reenviarCodigo($carta);
        Queue::assertPushed(JobEnvioCodigoAcessoCartaOferta::class, 2);
    }

    public function test_codigo_expira_apos_ttl(): void
    {
        Queue::fake();
        $service = app(CartaOfertaAcessoPublicoService::class);
        $carta = $this->criarCarta('pub-4');
        $service->solicitarCodigo($carta, '12345678909');

        $this->travel(CartaOfertaAcessoPublicoService::CODIGO_TTL_MINUTOS + 1)->minutes();

        $this->assertSame(0, $service->statusAcesso($carta)['codigo_segundos']);
        $this->expectExceptionMessage('Código expirado');
        $service->validarCodigo($carta, 'AAAAAAAA');
    }

    public function test_bloqueia_apos_tentativas_invalidas(): void
    {
        Queue::fake();
        $service = app(CartaOfertaAcessoPublicoService::class);
        $carta = $this->criarCarta('pub-5');
        $service->solicitarCodigo($carta, '12345678909');

        $codigo = null;
        Queue::assertPushed(JobEnvioCodigoAcessoCartaOferta::class, function ($j) use (&$codigo) {
            $codigo = $j->mail['codigo'];
            return true;
        });

        for ($i = 0; $i < CartaOfertaAcessoPublicoService::CODIGO_MAX_TENTATIVAS; $i++) {
            try {
                $service->validarCodigo($carta, '00000000');
                $this->fail('Código inválido deveria falhar.');
            } catch (\RuntimeException $e) {
            }
        }

        $status = $service->statusAcesso($carta);
        $this->assertSame(CartaOfertaAcessoPublicoService::CODIGO_BLOQUEIO_MINUTOS * 60, $status['bloqueio_segundos']);
        $this->assertSame(0, $status['tentativas_restantes']);

        // Bloqueado: nem o código correto passa.
        try {
            $service->validarCodigo($carta, $codigo);
            $this->fail('Deveria estar bloqueado.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Muitas tentativas', $e->getMessage());
        }

        $this->travel(CartaOfertaAcessoPublicoService::CODIGO_BLOQUEIO_MINUTOS + 1)->minutes();
        $this->assertSame(0, $service->statusAcesso($carta)['bloqueio_segundos']);
        $this->assertSame(
            CartaOfertaAcessoPublicoService::CODIGO_MAX_TENTATIVAS,
            $service->statusAcesso($carta)['tentativas_restantes']
        );
    }

    public function test_cpf_com_digito_verificador_invalido_nao_envia_codigo(): void
    {
        Queue::fake();
        $service = app(CartaOfertaAcessoPublicoService::class);
        $carta = $this->criarCarta('pub-6');

        try {
            $service->solicitarCodigo($carta, '123.456.789-00');
            $this->fail('CPF com dígito verificador inválido deveria falhar.');
        } catch (\RuntimeException $e) {
            $this->assertSame('CPF inválido.', $e->getMessage());
        }

        Queue::assertNothingPushed();
    }
}
