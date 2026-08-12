<?php

namespace Tests\Unit\Services\AtaReuniao;

use App\Jobs\AtaReuniao\SendAtaReuniaoPendenciaMailJob;
use App\Models\AtaReuniao;
use App\Models\AtaReuniaoCompartilhamentoExterno;
use App\Models\AtaReuniaoAcao;
use App\Models\User;
use App\Services\AtaReuniao\AtaReuniaoAprovacaoService;
use App\Services\AtaReuniao\AtaReuniaoAccessService;
use App\Services\AtaReuniao\AtaReuniaoCompartilhamentoService;
use App\Services\AtaReuniao\AtaReuniaoPendenciaNotificacaoService;
use App\Services\AtaReuniao\AtaReuniaoRelatorioExportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class AtaReuniaoMvpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->criarSchemaMinimo();
        $this->registrarGates();
    }

    public function testUsuarioNaoAutorizadoNaoVisualizaAtaRestrita(): void
    {
        $criador = $this->criarUsuario('criador@test.com', 10);
        $intruso = $this->criarUsuario('intruso@test.com', 10);
        $ata = $this->criarAta($criador->id, 10);

        $this->actingAs($intruso);

        $this->assertFalse(app(AtaReuniaoAccessService::class)->canView($intruso, $ata));
    }

    public function testPendenciaComVencimentoEmDoisDiasCriaNotificacaoD2(): void
    {
        Queue::fake();

        $responsavel = $this->criarUsuario('responsavel@test.com', 10);
        $ata = $this->criarAta($responsavel->id, 10);
        $this->criarPendencia($ata->id, 10, $responsavel->id, '2026-08-20', 'andamento');

        $total = app(AtaReuniaoPendenciaNotificacaoService::class)
            ->notificarD2(10, Carbon::parse('2026-08-18'));

        $this->assertSame(1, $total);
        $this->assertDatabaseHas('ata_reuniao_notificacoes', [
            'empresa_id' => 10,
            'tipo' => AtaReuniaoPendenciaNotificacaoService::TIPO_D2,
            'data_prazo_referencia' => '2026-08-20 00:00:00',
            'destinatario_id' => $responsavel->id,
        ]);
        Queue::assertPushed(SendAtaReuniaoPendenciaMailJob::class);
    }

    public function testPendenciaConcluidaNaoCriaNotificacaoD2(): void
    {
        Queue::fake();

        $responsavel = $this->criarUsuario('responsavel@test.com', 10);
        $ata = $this->criarAta($responsavel->id, 10);
        $this->criarPendencia($ata->id, 10, $responsavel->id, '2026-08-20', 'concluida');

        $total = app(AtaReuniaoPendenciaNotificacaoService::class)
            ->notificarD2(10, Carbon::parse('2026-08-18'));

        $this->assertSame(0, $total);
        $this->assertDatabaseCount('ata_reuniao_notificacoes', 0);
        Queue::assertNothingPushed();
    }

    public function testPrazoAlteradoPermiteNovaNotificacaoParaNovaData(): void
    {
        Queue::fake();

        $responsavel = $this->criarUsuario('responsavel@test.com', 10);
        $ata = $this->criarAta($responsavel->id, 10);
        $pendenciaId = $this->criarPendencia($ata->id, 10, $responsavel->id, '2026-08-20', 'andamento');
        $service = app(AtaReuniaoPendenciaNotificacaoService::class);

        $service->notificarD2(10, Carbon::parse('2026-08-18'));

        DB::table('ata_reuniao_acaos')->where('id', $pendenciaId)->update(['prazo' => '2026-08-21']);

        $total = $service->notificarD2(10, Carbon::parse('2026-08-19'));

        $this->assertSame(1, $total);
        $this->assertDatabaseHas('ata_reuniao_notificacoes', [
            'ata_reuniao_acao_id' => $pendenciaId,
            'data_prazo_referencia' => '2026-08-20 00:00:00',
        ]);
        $this->assertDatabaseHas('ata_reuniao_notificacoes', [
            'ata_reuniao_acao_id' => $pendenciaId,
            'data_prazo_referencia' => '2026-08-21 00:00:00',
        ]);
    }

    public function testPendenciaVencidaMudaParaAtrasada(): void
    {
        $responsavel = $this->criarUsuario('responsavel@test.com', 10);
        $ata = $this->criarAta($responsavel->id, 10);
        $pendenciaId = $this->criarPendencia($ata->id, 10, $responsavel->id, '2026-08-10', 'andamento');

        $total = app(AtaReuniaoPendenciaNotificacaoService::class)
            ->marcarAtrasadas(10, Carbon::parse('2026-08-18'));

        $this->assertSame(1, $total);
        $this->assertDatabaseHas('ata_reuniao_acaos', [
            'id' => $pendenciaId,
            'status' => 'atrasada',
        ]);
    }

    public function testConfiguracaoGeraNotificacaoNoVencimentoEAtrasoEscalonado(): void
    {
        Queue::fake();

        $responsavel = $this->criarUsuario('responsavel@test.com', 10);
        $ata = $this->criarAta($responsavel->id, 10);
        $pendenciaVencimentoId = $this->criarPendencia($ata->id, 10, $responsavel->id, '2026-08-18', 'andamento');
        $pendenciaAtrasoId = $this->criarPendencia($ata->id, 10, $responsavel->id, '2026-08-17', 'andamento');

        $totais = app(AtaReuniaoPendenciaNotificacaoService::class)
            ->notificarConfiguradas(10, Carbon::parse('2026-08-18'));

        $this->assertSame(1, $totais['vencimento']);
        $this->assertSame(1, $totais['atrasos']);
        $this->assertDatabaseHas('ata_reuniao_notificacoes', [
            'ata_reuniao_acao_id' => $pendenciaVencimentoId,
            'tipo' => AtaReuniaoPendenciaNotificacaoService::TIPO_VENCIMENTO,
        ]);
        $this->assertDatabaseHas('ata_reuniao_notificacoes', [
            'ata_reuniao_acao_id' => $pendenciaAtrasoId,
            'tipo' => AtaReuniaoPendenciaNotificacaoService::TIPO_ATRASO_PREFIXO . '1',
        ]);
    }

    public function testDiasUteisAntecipamNotificacaoParaUltimoDiaUtil(): void
    {
        Queue::fake();

        DB::table('ata_reuniao_notificacao_configs')->insert([
            'empresa_id' => 10,
            'usar_dias_uteis' => true,
            'dias_antecedencia' => 2,
            'horario_envio' => '07:00:00',
            'timezone' => 'America/Sao_Paulo',
            'incluir_gestor_copia' => false,
            'reenviar_no_vencimento' => false,
            'cobrar_apos_atraso' => false,
            'dias_escalonamento' => json_encode([1]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $responsavel = $this->criarUsuario('responsavel@test.com', 10);
        $ata = $this->criarAta($responsavel->id, 10);
        DB::table('feriados')->insert([
            'empresa_id' => 10,
            'descricao' => 'Feriado teste',
            'data' => '2026-08-14',
            'ativo' => true,
        ]);
        $this->criarPendencia($ata->id, 10, $responsavel->id, '2026-08-17', 'andamento');

        $total = app(AtaReuniaoPendenciaNotificacaoService::class)
            ->notificarD2(10, Carbon::parse('2026-08-12'));

        $this->assertSame(1, $total);
    }

    public function testGestorDaAreaEntraEmCopiaQuandoConfigurado(): void
    {
        Queue::fake();

        DB::table('ata_reuniao_notificacao_configs')->insert([
            'empresa_id' => 10,
            'usar_dias_uteis' => false,
            'dias_antecedencia' => 2,
            'horario_envio' => '07:00:00',
            'timezone' => 'America/Sao_Paulo',
            'incluir_gestor_copia' => true,
            'reenviar_no_vencimento' => true,
            'cobrar_apos_atraso' => false,
            'dias_escalonamento' => json_encode([1]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gestor = $this->criarUsuario('gestor@test.com', 10);
        $responsavel = $this->criarUsuario('responsavel@test.com', 10);
        $areaId = DB::table('area_etiquetas')->insertGetId([
            'label' => 'Qualidade',
            'ativo' => true,
            'empresa_id' => 10,
            'gestor_id' => $gestor->id,
        ]);
        $ata = $this->criarAta($responsavel->id, 10);
        DB::table('ata_reuniaos')->where('id', $ata->id)->update(['area_etiqueta_id' => $areaId]);
        $pendenciaId = $this->criarPendencia($ata->id, 10, $responsavel->id, '2026-08-18', 'andamento');

        app(AtaReuniaoPendenciaNotificacaoService::class)->notificarConfiguradas(10, Carbon::parse('2026-08-18'));

        $notificacao = \App\Models\AtaReuniaoNotificacao::where('ata_reuniao_acao_id', $pendenciaId)->firstOrFail();

        $this->assertSame('gestor@test.com', $notificacao->payload['cc'][0]['email']);
    }

    public function testRelatorioExportServiceRetornaLinhasSanitizadas(): void
    {
        $responsavel = $this->criarUsuario('responsavel@test.com', 10);
        DB::table('users')->where('id', $responsavel->id)->update(['nome' => 'Responsavel']);
        $ata = $this->criarAta($responsavel->id, 10);
        $this->criarPendencia($ata->id, 10, $responsavel->id, '2026-08-20', 'andamento');

        $service = app(AtaReuniaoRelatorioExportService::class);
        $dados = $service->dados($responsavel);

        $this->assertNotEmpty($dados);
        $this->assertSame('Ata de teste', $dados[0]['titulo_ata']);
        $this->assertSame('Responsavel', $dados[0]['responsavel']);
        $this->assertCount(12, $service->headers());
    }

    public function testAtaAprovadaBloqueiaEdicaoComum(): void
    {
        $criador = $this->criarUsuario('criador@test.com', 10);
        $ata = $this->criarAta($criador->id, 10, AtaReuniao::STATUS_APROVADA);

        $this->actingAs($criador);

        $this->assertFalse(app(AtaReuniaoAccessService::class)->canEdit($criador, $ata));
    }

    public function testResponsavelSemAcessoRecebeAcessoMinimoDaPendencia(): void
    {
        $criador = $this->criarUsuario('criador@test.com', 10);
        $responsavel = $this->criarUsuario('responsavel@test.com', 10);
        $ata = $this->criarAta($criador->id, 10);
        $service = app(AtaReuniaoAccessService::class);

        $this->actingAs($responsavel);
        $this->assertFalse($service->canView($responsavel, $ata));

        $service->concederAcessoMinimoPendencia($ata, $responsavel);

        $this->assertTrue($service->canView($responsavel, $ata));
    }

    public function testAprovacaoParalelaAprovaEBloqueiaAta(): void
    {
        $criador = $this->criarUsuario('criador@test.com', 10);
        $aprovador = $this->criarUsuario('aprovador@test.com', 10);
        $ata = $this->criarAta($criador->id, 10);
        $service = app(AtaReuniaoAprovacaoService::class);

        $this->actingAs($criador);
        $total = $service->solicitar($ata, $criador, [$aprovador->id]);

        $this->assertSame(1, $total);
        $this->assertDatabaseHas('ata_reuniao_aprovacoes', [
            'ata_reuniao_id' => $ata->id,
            'aprovador_id' => $aprovador->id,
            'status' => 'pendente',
        ]);

        $this->actingAs($aprovador);
        $service->decidir($ata->fresh(), $aprovador, 'aprovado', 'Aprovado');

        $this->assertDatabaseHas('ata_reuniaos', [
            'id' => $ata->id,
            'status' => AtaReuniao::STATUS_APROVADA,
            'versao_atual' => '1.0',
        ]);
        $this->assertFalse(app(AtaReuniaoAccessService::class)->canEdit($criador, $ata->fresh()));
    }

    public function testLinkExternoAssinadoExpiraEm24HorasEPodeSerRevogado(): void
    {
        $criador = $this->criarUsuario('criador@test.com', 10);
        $ata = $this->criarAta($criador->id, 10);
        $service = app(AtaReuniaoCompartilhamentoService::class);

        $this->actingAs($criador);
        $link = $service->criarLink($ata, $criador, 'externo@test.com', 'Convidado Externo');

        $this->assertArrayHasKey('url', $link);
        $this->assertTrue(now()->addHours(23)->lessThan($link['expira_em']));
        $this->assertTrue(now()->addHours(25)->greaterThan($link['expira_em']));

        $token = basename((string) parse_url($link['url'], PHP_URL_PATH));
        $compartilhamento = $service->resolver($token);

        $this->assertInstanceOf(AtaReuniaoCompartilhamentoExterno::class, $compartilhamento);

        $service->revogar($compartilhamento, $criador);

        $this->assertNull($service->resolver($token));
    }

    private function criarSchemaMinimo(): void
    {
        Schema::dropIfExists('ata_reuniao_notificacoes');
        Schema::dropIfExists('ata_reuniao_notificacao_configs');
        Schema::dropIfExists('ata_reuniao_eventos');
        Schema::dropIfExists('ata_reuniao_compartilhamentos_externos');
        Schema::dropIfExists('ata_reuniao_versoes');
        Schema::dropIfExists('ata_reuniao_aprovacoes');
        Schema::dropIfExists('ata_reuniao_acessos');
        Schema::dropIfExists('ata_reuniao_tipos');
        Schema::dropIfExists('ata_reuniao_assuntos');
        Schema::dropIfExists('ata_reuniao_participantes');
        Schema::dropIfExists('ata_reuniao_acaos');
        Schema::dropIfExists('ata_reuniaos');
        Schema::dropIfExists('area_etiquetas');
        Schema::dropIfExists('feriados');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('login')->nullable();
            $table->string('password')->nullable();
            $table->string('tipo')->default(User::FUNCIONARIO);
            $table->unsignedBigInteger('grupo_id')->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('temp')->default(false);
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->boolean('require_password_reset')->nullable();
            $table->unsignedInteger('password_reset_days')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ata_reuniaos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();
            $table->uuid('uuid_publico')->nullable();
            $table->unsignedBigInteger('quem_cadastrou');
            $table->string('titulo')->nullable();
            $table->text('objetivo')->nullable();
            $table->string('status')->default(AtaReuniao::STATUS_RASCUNHO);
            $table->string('nivel_acesso')->default('privada');
            $table->string('classificacao_confidencialidade')->default('uso_interno');
            $table->unsignedBigInteger('organizador_id')->nullable();
            $table->unsignedBigInteger('redator_id')->nullable();
            $table->string('aprovacao_modo')->default('paralela');
            $table->string('versao_atual')->default('0.1');
            $table->string('timezone')->default('America/Sao_Paulo');
            $table->string('local');
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim');
            $table->timestamp('aprovada_em')->nullable();
            $table->timestamp('publicada_em')->nullable();
            $table->timestamp('bloqueada_em')->nullable();
            $table->timestamp('cancelada_em')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('area_etiqueta_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('area_etiquetas', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->boolean('ativo')->default(true);
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('gestor_id')->nullable();
        });

        Schema::create('feriados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('descricao');
            $table->date('data');
            $table->boolean('ativo')->default(true);
        });

        Schema::create('ata_reuniao_acaos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->string('titulo')->nullable();
            $table->longText('descricao')->nullable();
            $table->string('responsavel')->nullable();
            $table->unsignedBigInteger('responsavel_id')->nullable();
            $table->string('email')->nullable();
            $table->longText('acao');
            $table->date('prazo')->nullable();
            $table->boolean('continuo')->nullable();
            $table->string('observacao')->nullable();
            $table->string('status');
            $table->string('prioridade')->default('media');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ata_reuniao_participantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->string('nome')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('funcao')->nullable();
        });

        Schema::create('ata_reuniao_assuntos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->longText('assunto');
        });

        Schema::create('ata_reuniao_tipos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->string('tipo');
            $table->longText('observacao')->nullable();
        });

        Schema::create('ata_reuniao_acessos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->unsignedBigInteger('user_id');
            $table->string('papel');
            $table->string('origem')->default('manual');
            $table->timestamp('expira_em')->nullable();
            $table->timestamp('revogado_em')->nullable();
            $table->timestamps();
        });

        Schema::create('ata_reuniao_notificacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->unsignedBigInteger('ata_reuniao_acao_id')->nullable();
            $table->unsignedBigInteger('destinatario_id')->nullable();
            $table->string('canal')->default('email');
            $table->string('tipo');
            $table->string('modo_disparo')->default('automatico');
            $table->string('status')->default('pendente');
            $table->date('data_prazo_referencia')->nullable();
            $table->string('destinatario_nome')->nullable();
            $table->string('destinatario_email')->nullable();
            $table->string('assunto')->nullable();
            $table->json('payload')->nullable();
            $table->text('erro')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamps();
        });

        Schema::create('ata_reuniao_notificacao_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->boolean('usar_dias_uteis')->default(false);
            $table->unsignedTinyInteger('dias_antecedencia')->default(2);
            $table->time('horario_envio')->default('07:00:00');
            $table->string('timezone')->default('America/Sao_Paulo');
            $table->boolean('incluir_gestor_copia')->default(false);
            $table->boolean('reenviar_no_vencimento')->default(true);
            $table->boolean('cobrar_apos_atraso')->default(true);
            $table->json('dias_escalonamento')->nullable();
            $table->timestamps();
        });

        Schema::create('ata_reuniao_aprovacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->unsignedBigInteger('aprovador_id');
            $table->string('versao')->default('0.1');
            $table->string('status')->default('pendente');
            $table->string('decisao')->nullable();
            $table->text('comentario')->nullable();
            $table->timestamp('respondido_em')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ata_reuniao_versoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->string('numero');
            $table->unsignedBigInteger('autor_id')->nullable();
            $table->text('descricao')->nullable();
            $table->json('campos_alterados')->nullable();
            $table->json('snapshot')->nullable();
            $table->timestamps();
        });

        Schema::create('ata_reuniao_compartilhamentos_externos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->string('token_hash');
            $table->string('nome_externo')->nullable();
            $table->string('email_externo')->nullable();
            $table->string('escopo')->default('leitura');
            $table->unsignedBigInteger('criado_por')->nullable();
            $table->timestamp('expira_em');
            $table->timestamp('revogado_em')->nullable();
            $table->timestamp('ultimo_acesso_em')->nullable();
            $table->timestamps();
        });

        Schema::create('ata_reuniao_eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('ata_reuniao_id');
            $table->unsignedBigInteger('ator_id')->nullable();
            $table->string('ator_tipo')->default('user');
            $table->string('tipo_evento');
            $table->string('entidade_tipo')->nullable();
            $table->unsignedBigInteger('entidade_id')->nullable();
            $table->json('dados')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    private function registrarGates(): void
    {
        Gate::define('administracao_atareuniao_privilegio_adm', fn (User $user) => $user->login === 'admin@test.com');
        Gate::define('administracao_atareuniao_update', fn (User $user) => true);
    }

    private function criarUsuario(string $email, int $empresaId): User
    {
        $id = DB::table('users')->insertGetId([
            'nome' => $email,
            'login' => $email,
            'tipo' => User::FUNCIONARIO,
            'ativo' => true,
            'empresa_id' => $empresaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::findOrFail($id);
    }

    private function criarAta(int $criadorId, int $empresaId, string $status = AtaReuniao::STATUS_RASCUNHO): AtaReuniao
    {
        $id = DB::table('ata_reuniaos')->insertGetId([
            'codigo' => 'ATA-2026-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'quem_cadastrou' => $criadorId,
            'organizador_id' => $criadorId,
            'titulo' => 'Ata de teste',
            'status' => $status,
            'local' => 'Online',
            'data_inicio' => '2026-08-18 09:00:00',
            'data_fim' => '2026-08-18 10:00:00',
            'empresa_id' => $empresaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return AtaReuniao::withoutGlobalScopes()->findOrFail($id);
    }

    private function criarPendencia(int $ataId, int $empresaId, int $responsavelId, string $prazo, string $status): int
    {
        return DB::table('ata_reuniao_acaos')->insertGetId([
            'empresa_id' => $empresaId,
            'ata_reuniao_id' => $ataId,
            'titulo' => 'Pendencia de teste',
            'descricao' => 'Atualizar status da pendencia',
            'responsavel' => 'Responsavel',
            'responsavel_id' => $responsavelId,
            'email' => 'responsavel@test.com',
            'acao' => 'Atualizar status da pendencia',
            'prazo' => $prazo,
            'continuo' => false,
            'status' => $status,
            'prioridade' => 'alta',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
