<?php

namespace Tests\Unit\Domain\SaudeOcupacional;

use App\Domain\SaudeOcupacional\ModuloSaudeOcupacional;
use App\Domain\SaudeOcupacional\Seguranca\PerfilAcessoSaude;
use App\Http\Middleware\VerificaModuloSaudeOcupacional;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SegurancaSaudeOcupacionalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        Schema::dropIfExists('cliente_configs');
        Schema::create('cliente_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->boolean('modulo_saude_ocupacional')->default(false);
        });
    }

    public function test_rh_sem_perfil_saude_nao_recebe_campos_sensiveis(): void
    {
        $this->definirHabilidades(['saude_ocupacional']);
        $perfil = new PerfilAcessoSaude();
        $user = $this->usuario(10);

        $dados = $perfil->ocultarCamposSensiveis(
            ['colaborador' => 'Fulano', 'data_inicio' => '01/11/2026', 'cid' => 'M54', 'laudo' => 'x'],
            ['cid', 'laudo'],
            $user
        );

        $this->assertTrue($perfil->podeAcessar($user));
        $this->assertFalse($perfil->podeVerDadosSensiveis($user));
        $this->assertSame(['colaborador' => 'Fulano', 'data_inicio' => '01/11/2026'], $dados);
    }

    public function test_perfil_saude_recebe_campos_sensiveis(): void
    {
        $this->definirHabilidades(['saude_ocupacional', 'saude_ocupacional_dados_sensiveis']);
        $perfil = new PerfilAcessoSaude();

        $dados = $perfil->ocultarCamposSensiveis(['cid' => 'M54'], ['cid'], $this->usuario(10));

        $this->assertSame(['cid' => 'M54'], $dados);
    }

    public function test_sem_usuario_nada_e_permitido(): void
    {
        $perfil = new PerfilAcessoSaude();

        $this->assertFalse($perfil->podeAcessar(null));
        $this->assertSame([], $perfil->ocultarCamposSensiveis(['cid' => 'M54'], ['cid'], null));
    }

    public function test_modulo_segue_flag_da_empresa_e_isola_empresas(): void
    {
        DB::table('cliente_configs')->insert([
            ['cliente_id' => 10, 'modulo_saude_ocupacional' => true],
            ['cliente_id' => 20, 'modulo_saude_ocupacional' => false],
        ]);

        $this->assertTrue(ModuloSaudeOcupacional::habilitado(10));
        $this->assertFalse(ModuloSaudeOcupacional::habilitado(20));
        $this->assertFalse(ModuloSaudeOcupacional::habilitado(30), 'empresa sem config fica desligada');
    }

    public function test_limpar_cache_reflete_mudanca_da_flag(): void
    {
        DB::table('cliente_configs')->insert(['cliente_id' => 10, 'modulo_saude_ocupacional' => false]);
        $this->assertFalse(ModuloSaudeOcupacional::habilitado(10));

        DB::table('cliente_configs')->where('cliente_id', 10)->update(['modulo_saude_ocupacional' => true]);
        $this->assertFalse(ModuloSaudeOcupacional::habilitado(10), 'ainda em cache');

        ModuloSaudeOcupacional::limparCache();
        $this->assertTrue(ModuloSaudeOcupacional::habilitado(10));
    }

    public function test_middleware_bloqueia_quando_modulo_desligado(): void
    {
        DB::table('cliente_configs')->insert(['cliente_id' => 10, 'modulo_saude_ocupacional' => false]);
        $this->actingAs($this->usuario(10));

        $request = Request::create('/g/saude-ocupacional/painel/dados', 'GET');
        $request->headers->set('Accept', 'application/json');

        $resposta = (new VerificaModuloSaudeOcupacional())->handle($request, fn () => response('ok'));

        $this->assertSame(403, $resposta->getStatusCode());
    }

    public function test_middleware_bloqueia_pagina_quando_modulo_desligado(): void
    {
        DB::table('cliente_configs')->insert(['cliente_id' => 10, 'modulo_saude_ocupacional' => false]);
        $this->actingAs($this->usuario(10));

        $this->expectException(HttpException::class);

        (new VerificaModuloSaudeOcupacional())->handle(Request::create('/g/saude-ocupacional/painel', 'GET'), fn () => response('ok'));
    }

    public function test_middleware_libera_quando_modulo_ligado(): void
    {
        DB::table('cliente_configs')->insert(['cliente_id' => 10, 'modulo_saude_ocupacional' => true]);
        $this->actingAs($this->usuario(10));

        $resposta = (new VerificaModuloSaudeOcupacional())->handle(Request::create('/x', 'GET'), fn () => response('ok'));

        $this->assertSame('ok', $resposta->getContent());
    }

    /**
     * @param  list<string>  $permitidas
     */
    private function definirHabilidades(array $permitidas): void
    {
        $todas = [
            PerfilAcessoSaude::HABILIDADE_ACESSO,
            PerfilAcessoSaude::HABILIDADE_CONFIGURACAO,
            PerfilAcessoSaude::HABILIDADE_DADOS_SENSIVEIS,
        ];

        foreach ($todas as $habilidade) {
            Gate::define($habilidade, fn () => in_array($habilidade, $permitidas, true));
        }
    }

    private function usuario(int $empresaId): User
    {
        $user = new User();
        $user->forceFill(['id' => 1, 'empresa_id' => $empresaId, 'nome' => 'Tester']);
        $user->exists = true;

        return $user;
    }
}
