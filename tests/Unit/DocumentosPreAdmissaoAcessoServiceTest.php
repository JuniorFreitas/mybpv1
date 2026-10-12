<?php

namespace Tests\Unit;

use App\Jobs\JobEnvioCodigoAcessoCartaOferta;
use App\Models\ResultadoIntegrado;
use App\Services\AcessoPublico\CodigoAcessoService;
use App\Services\Preadmissao\DocumentosPreAdmissaoAcessoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class DocumentosPreAdmissaoAcessoServiceTest extends TestCase
{
    private const EMPRESA_ID = 10;
    private const CPF = '123.456.789-09';

    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false, 'app.url' => 'https://mybp.test']);

        foreach (['acessos_publicos_auditorias', 'resultado_integrados', 'feedback_curriculos', 'curriculos', 'clientes'] as $tabela) {
            Schema::dropIfExists($tabela);
        }

        (require database_path('migrations/2026_10_12_100000_create_acessos_publicos_auditorias_table.php'))->up();

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('apelido')->nullable();
            $table->string('cnpj')->nullable();
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

        Schema::create('feedback_curriculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id');
            $table->unsignedBigInteger('empresa_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('resultado_integrados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->uuid('public_id')->nullable()->unique();
            $table->string('acesso_codigo_hash')->nullable();
            $table->timestamp('acesso_codigo_expira_em')->nullable();
            $table->timestamp('acesso_codigo_usado_em')->nullable();
            $table->boolean('documentos_entregue')->default(false);
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->timestamps();
        });

        DB::table('clientes')->insert(['id' => self::EMPRESA_ID, 'apelido' => 'empresa-teste', 'nome_fantasia' => 'Empresa']);
        DB::table('clientes')->insert(['id' => 20, 'apelido' => 'outra', 'nome_fantasia' => 'Outra']);
    }

    private function criarSolicitacao(int $id, string $cpf = self::CPF, int $empresaId = self::EMPRESA_ID, bool $documentos = true, ?string $publicId = null): ResultadoIntegrado
    {
        DB::table('curriculos')->insert(['id' => $id, 'nome' => 'Candidato ' . $id, 'cpf' => $cpf, 'email' => 'cand@teste.com']);
        DB::table('feedback_curriculos')->insert(['id' => $id, 'curriculo_id' => $id, 'empresa_id' => $empresaId]);
        DB::table('resultado_integrados')->insert([
            'id' => $id,
            'feedback_id' => $id,
            'documentos_entregue' => $documentos,
            'public_id' => $publicId,
            'usuario_id' => 99,
        ]);

        return ResultadoIntegrado::query()->findOrFail($id);
    }

    private function codigoEnviado(): string
    {
        $codigo = null;
        Queue::assertPushed(JobEnvioCodigoAcessoCartaOferta::class, function ($job) use (&$codigo) {
            $codigo = $job->mail['codigo'];
            $this->assertSame(DocumentosPreAdmissaoAcessoService::RECURSO, $job->mail['recurso']);

            return true;
        });

        return (string) $codigo;
    }

    public function test_link_com_public_id_envia_codigo_e_valida_uso_unico(): void
    {
        $this->freezeSecond();
        Queue::fake();
        $service = app(DocumentosPreAdmissaoAcessoService::class);
        $empresa = $service->encontrarEmpresa('empresa-teste');
        $publicId = (string) Str::uuid();
        $this->criarSolicitacao(1, self::CPF, self::EMPRESA_ID, true, $publicId);

        $resultado = $service->solicitarCodigo($empresa, '12345678909', $publicId);
        $registro = $resultado['registro'];

        $this->assertSame('c***@teste.com', $resultado['resposta']['email_mascarado']);
        $this->assertSame(CodigoAcessoService::CODIGO_TTL_MINUTOS * 60, $resultado['resposta']['acesso']['codigo_segundos']);

        $service->validarCodigo($registro, $this->codigoEnviado());
        $this->assertSame(1, $service->curriculoId($registro));

        // A tela pública não pode trocar o usuário responsável pelo registro do RH.
        $this->assertSame(99, (int) DB::table('resultado_integrados')->where('id', 1)->value('usuario_id'));

        $this->expectException(\RuntimeException::class);
        $service->validarCodigo($registro->fresh(), 'AAAAAAAA');
    }

    public function test_public_id_com_cpf_de_outra_pessoa_nao_envia_codigo(): void
    {
        Queue::fake();
        $service = app(DocumentosPreAdmissaoAcessoService::class);
        $empresa = $service->encontrarEmpresa('empresa-teste');
        $publicId = (string) Str::uuid();
        $this->criarSolicitacao(1, self::CPF, self::EMPRESA_ID, true, $publicId);

        try {
            $service->solicitarCodigo($empresa, '529.982.247-25', $publicId);
            $this->fail('CPF diferente do link deveria falhar.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('não confere', $e->getMessage());
        }

        Queue::assertNothingPushed();
    }

    public function test_link_geral_localiza_pelo_cpf_somente_na_empresa_e_com_documentos_solicitados(): void
    {
        Queue::fake();
        $service = app(DocumentosPreAdmissaoAcessoService::class);
        $empresa = $service->encontrarEmpresa('empresa-teste');

        $this->criarSolicitacao(1, self::CPF, 20);                          // outra empresa
        $this->criarSolicitacao(2, self::CPF, self::EMPRESA_ID, false);     // documentos não solicitados
        $this->assertNull($service->encontrarPorCpf(self::EMPRESA_ID, self::CPF));

        $this->criarSolicitacao(3, '12345678909');                          // CPF salvo só com dígitos
        $resultado = $service->solicitarCodigo($empresa, self::CPF);

        $this->assertSame(3, $resultado['registro']->id);
        Queue::assertPushed(JobEnvioCodigoAcessoCartaOferta::class, 1);
    }

    public function test_url_publica_gera_public_id_e_cai_no_link_geral_sem_solicitacao(): void
    {
        $service = app(DocumentosPreAdmissaoAcessoService::class);
        $this->criarSolicitacao(1);

        $url = $service->urlPublica(self::EMPRESA_ID, 1);
        $publicId = DB::table('resultado_integrados')->where('id', 1)->value('public_id');

        $this->assertTrue(Str::isUuid($publicId));
        $this->assertSame('https://mybp.test/empresa-teste/documentos/' . $publicId, $url);
        $this->assertSame($url, $service->urlPublica(self::EMPRESA_ID, 1), 'O link não muda a cada envio.');
        $this->assertSame('https://mybp.test/empresa-teste/documentos', $service->urlPublica(self::EMPRESA_ID, 555));
    }

    public function test_http_reenvio_e_validacao_exigem_cpf_confirmado_na_sessao(): void
    {
        Queue::fake();
        $this->criarSolicitacao(1);

        $this->postJson('/empresa-teste/documentos-pre-admissao/solicitar-codigo', [])
            ->assertStatus(422);
        $this->postJson('/empresa-teste/documentos-pre-admissao/validar-codigo', ['codigo' => 'AAAAAAAA'])
            ->assertStatus(422);
        Queue::assertNothingPushed();

        $this->postJson('/empresa-teste/documentos-pre-admissao/solicitar-codigo', ['cpf' => self::CPF])
            ->assertOk()
            ->assertJsonPath('email_mascarado', 'c***@teste.com');

        // Com o CPF confirmado, um código errado conta tentativa em vez de pedir o CPF de novo.
        $this->postJson('/empresa-teste/documentos-pre-admissao/validar-codigo', ['codigo' => '00000000'])
            ->assertStatus(422)
            ->assertJsonPath('acesso.tentativas_restantes', CodigoAcessoService::CODIGO_MAX_TENTATIVAS - 1);
    }

    public function test_http_salvar_documentos_sem_autenticar_retorna_401(): void
    {
        $this->criarSolicitacao(1);

        $this->putJson('/empresa-teste/documentos/1', ['id' => 1, 'nome' => 'X', 'filiacao_mae' => 'Y'])
            ->assertStatus(401);

        $this->assertSame('Candidato 1', DB::table('curriculos')->where('id', 1)->value('nome'));
    }

    public function test_http_grava_trilha_de_auditoria_com_cpf_mascarado_e_registro_integro(): void
    {
        Queue::fake();
        $this->criarSolicitacao(1);

        $this->postJson('/empresa-teste/documentos-pre-admissao/solicitar-codigo', ['cpf' => '529.982.247-25'])
            ->assertStatus(422);
        $this->postJson('/empresa-teste/documentos-pre-admissao/solicitar-codigo', ['cpf' => self::CPF])->assertOk();
        $this->postJson('/empresa-teste/documentos-pre-admissao/validar-codigo', ['codigo' => '00000000'])
            ->assertStatus(422);

        $eventos = \App\Models\AcessoPublicoAuditoria::query()->orderBy('id')->get();
        $this->assertSame(
            ['codigo_negado', 'codigo_solicitado', 'codigo_invalido'],
            $eventos->pluck('evento')->all()
        );

        $negado = $eventos[0];
        $this->assertSame('***.982.247-**', $negado->cpf_mascarado);
        $this->assertNull($negado->registro_id);

        $solicitado = $eventos[1];
        $this->assertSame(1, $solicitado->registro_id);
        $this->assertSame('***.456.789-**', $solicitado->cpf_mascarado);
        $this->assertSame('c***@teste.com', $solicitado->email_mascarado);
        $this->assertNotEmpty($solicitado->ip);
        $this->assertStringNotContainsString('12345678909', json_encode($eventos));

        // Encadeamento: cada evento do registro aponta para o hash do anterior.
        $this->assertSame($solicitado->hash, $eventos[2]->hash_anterior);

        $auditoria = app(\App\Services\AcessoPublico\AuditoriaAcessoService::class);
        $this->assertTrue($auditoria->integro($solicitado->fresh()));

        DB::table('acessos_publicos_auditorias')->where('id', $solicitado->id)->update(['ip' => '10.0.0.9']);
        $this->assertFalse($auditoria->integro($solicitado->fresh()), 'Alteração direta no banco precisa ser detectada.');
    }

    public function test_registro_de_auditoria_nao_pode_ser_alterado_nem_excluido(): void
    {
        $registro = app(\App\Services\AcessoPublico\AuditoriaAcessoService::class)
            ->registrar('teste', self::EMPRESA_ID, 'evento');

        try {
            $registro->update(['evento' => 'outro']);
            $this->fail('Não deveria permitir alterar.');
        } catch (\LogicException $e) {
        }

        $this->expectException(\LogicException::class);
        $registro->delete();
    }

    public function test_http_salvar_exige_aceite_do_termo_vigente(): void
    {
        $this->criarSolicitacao(1);
        $sessao = ['documentos_pre_adm_empresa-teste' => ['resultado_id' => 1, 'autenticado' => true]];
        $corpo = ['nome' => 'Novo Nome', 'filiacao_mae' => 'Mae'];

        $this->withSession($sessao)->putJson('/empresa-teste/documentos/1', $corpo)->assertStatus(422);
        $this->withSession($sessao)
            ->putJson('/empresa-teste/documentos/1', $corpo + ['termo_aceito' => true, 'termo_hash' => 'hash-antigo'])
            ->assertStatus(422);

        $this->assertSame('Candidato 1', DB::table('curriculos')->where('id', 1)->value('nome'));
        $this->assertSame(0, DB::table('acessos_publicos_auditorias')->where('evento', 'termo_aceito')->count());
    }

    public function test_termo_muda_de_hash_quando_o_texto_muda(): void
    {
        $termos = app(\App\Services\Preadmissao\TermoDocumentosPreAdmissao::class);
        $service = app(DocumentosPreAdmissaoAcessoService::class);

        $a = $termos->montar($service->encontrarEmpresa('empresa-teste'));
        $b = $termos->montar($service->encontrarEmpresa('outra'));

        $this->assertSame(64, strlen($a['hash']));
        $this->assertNotSame($a['hash'], $b['hash'], 'O controlador faz parte do texto aceito.');
    }

    public function test_http_ja_recebi_o_codigo_confere_cpf_sem_enviar_outro_codigo(): void
    {
        Queue::fake();
        $this->criarSolicitacao(1);
        $url = '/empresa-teste/documentos-pre-admissao/solicitar-codigo';

        $this->postJson($url, ['cpf' => '529.982.247-25', 'ja_tenho_codigo' => true])->assertStatus(422);

        // Sem código emitido: abre a etapa do código já como expirado (para reenviar).
        $this->postJson($url, ['cpf' => self::CPF, 'ja_tenho_codigo' => true])
            ->assertOk()
            ->assertJsonPath('acesso.codigo_segundos', 0)
            ->assertJsonPath('email_mascarado', 'c***@teste.com');
        Queue::assertNothingPushed();

        // CPF confirmado na sessão: o reenvio (sem CPF) passa a funcionar.
        $this->postJson($url, [])->assertOk();
        Queue::assertPushed(JobEnvioCodigoAcessoCartaOferta::class, 1);

        $this->assertSame(
            ['codigo_negado', 'cpf_confirmado', 'codigo_reenviado'],
            DB::table('acessos_publicos_auditorias')->orderBy('id')->pluck('evento')->all()
        );
    }

    public function test_mascara_de_email_deixa_o_dono_reconhecer_sem_expor_o_endereco(): void
    {
        $codigos = app(CodigoAcessoService::class);

        $this->assertSame('al***a@outlook.com', $codigos->mascararEmail('alessandra.sousa@outlook.com'));
        $this->assertSame('jo***o@empresa.com.br', $codigos->mascararEmail('joao.pedro@empresa.com.br'));
        $this->assertSame('c***@teste.com', $codigos->mascararEmail('cand@teste.com'));
        $this->assertSame('a***@x.com', $codigos->mascararEmail('a@x.com'));
        $this->assertSame('***', $codigos->mascararEmail('sem-arroba'));
    }
}
