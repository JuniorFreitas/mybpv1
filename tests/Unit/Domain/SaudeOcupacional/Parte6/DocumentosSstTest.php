<?php

namespace Tests\Unit\Domain\SaudeOcupacional\Parte6;

use App\Domain\SaudeOcupacional\Documentos\ArquivamentoAutomaticoSst;
use App\Domain\SaudeOcupacional\Documentos\DocumentosSstService;
use App\Domain\SaudeOcupacional\Documentos\SituacaoDocumentoSst;
use App\Models\Arquivo;
use App\Models\DocumentoParaAssinatura;
use App\Models\Examesesmt;
use App\Models\ExamesesmtAso;
use App\Models\SaudeDocumento;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Unit\Domain\SaudeOcupacional\CriaTabelasSaudeOcupacional;

class DocumentosSstTest extends TestCase
{
    use CriaTabelasSaudeOcupacional;

    private const EMPRESA = 10;

    private const OUTRA = 20;

    private User $rh;

    private int $colaborador;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Storage::fake(Arquivo::DISCO_SAUDE_DOCUMENTOS);
        Storage::fake(Arquivo::DISCO_DOCUMENTO_ASSINATURA);
        $this->criarTabelasSaude();

        Schema::dropIfExists('arquivos');
        Schema::create('arquivos', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('quem_enviou')->nullable();
            $t->string('nome')->nullable();
            $t->boolean('imagem')->default(false);
            $t->string('layout')->nullable();
            $t->string('extensao')->nullable();
            $t->string('file')->nullable();
            $t->string('thumb')->nullable();
            $t->unsignedBigInteger('bytes')->nullable();
            $t->boolean('temporario')->default(false);
            $t->string('chave')->nullable();
            $t->string('disco')->nullable();
            $t->timestamps();
        });

        DB::table('cliente_configs')->insert(['cliente_id' => self::EMPRESA, 'modulo_saude_ocupacional' => true]);
        $curriculo = DB::table('curriculos')->insertGetId(['nome' => 'Maria Teste']);
        $this->colaborador = DB::table('feedback_curriculos')->insertGetId(['empresa_id' => self::EMPRESA, 'curriculo_id' => $curriculo]);

        $this->rh = new User();
        $this->rh->forceFill(['empresa_id' => self::EMPRESA, 'nome' => 'RH', 'email' => 'rh@example.test'])->save();
        $this->perfilSaude(false);
    }

    #[DataProvider('situacoes')]
    public function test_situacao_pela_vigencia(?string $fim, string $esperada): void
    {
        $this->assertSame($esperada, SituacaoDocumentoSst::de($fim, CarbonImmutable::parse('2026-11-05')));
    }

    public static function situacoes(): array
    {
        return [
            'sem fim' => [null, SituacaoDocumentoSst::SEM_VALIDADE],
            'ontem' => ['2026-11-04', SituacaoDocumentoSst::VENCIDO],
            'hoje ainda vale' => ['2026-11-05', SituacaoDocumentoSst::VENCENDO],
            'limite da janela' => ['2027-01-04', SituacaoDocumentoSst::VENCENDO],
            'depois da janela' => ['2027-01-05', SituacaoDocumentoSst::VIGENTE],
        ];
    }

    public function test_envia_versiona_e_lista_com_situacao_e_totais(): void
    {
        $svc = app(DocumentosSstService::class);
        $doc = $svc->criar(self::EMPRESA, ['tipo' => 'ltcat', 'titulo' => 'LTCAT Matriz', 'vigencia_inicio' => '01/01/2026', 'vigencia_fim' => '20/11/2026'], $this->pdf(), $this->rh);
        $svc->criar(self::EMPRESA, ['tipo' => 'ordem_servico', 'titulo' => 'OS Operador'], $this->pdf(), $this->rh);
        $svc->criar(self::OUTRA, ['tipo' => 'ltcat', 'titulo' => 'De outra empresa'], $this->pdf(), $this->rh);

        $versao = $svc->novaVersao(self::EMPRESA, $doc->id, ['vigencia_inicio' => '2026-11-01', 'vigencia_fim' => '2027-12-31'], $this->pdf('nova'), $this->rh);
        $this->assertSame(2, $versao->versao);

        $lista = $svc->listar(self::EMPRESA, [], $this->rh, CarbonImmutable::parse('2026-11-05'));
        $this->assertSame(2, $lista['total']);
        $ltcat = collect($lista['itens'])->firstWhere('id', $doc->id);
        $this->assertSame(2, $ltcat['versao']);
        $this->assertSame(SituacaoDocumentoSst::VIGENTE, $ltcat['situacao'], 'A situação vem da versão atual.');
        $this->assertSame(['vigente' => 1, 'vencendo' => 0, 'vencido' => 0, 'sem_validade' => 1], $lista['totais']);

        $semValidade = $svc->listar(self::EMPRESA, ['situacao' => 'sem_validade'], $this->rh, CarbonImmutable::parse('2026-11-05'));
        $this->assertSame(['OS Operador'], array_column($semValidade['itens'], 'titulo'));

        // Arquivo no disco privado, em pasta da empresa, com nome aleatório.
        $arquivo = Arquivo::query()->find($versao->arquivo_id);
        $this->assertSame(Arquivo::DISCO_SAUDE_DOCUMENTOS, $arquivo->disco);
        $this->assertStringStartsWith('empresa_' . self::EMPRESA . '/', $arquivo->file);
        Storage::disk(Arquivo::DISCO_SAUDE_DOCUMENTOS)->assertExists($arquivo->file);
        $this->assertSame(64, strlen((string) $versao->hash_sha256));
    }

    public function test_vencendo_entra_no_painel_e_no_filtro(): void
    {
        $svc = app(DocumentosSstService::class);
        $svc->criar(self::EMPRESA, ['tipo' => 'pgr', 'titulo' => 'PGR', 'vigencia_fim' => '2026-12-01'], $this->pdf(), $this->rh);
        $svc->criar(self::EMPRESA, ['tipo' => 'ltcat', 'titulo' => 'LTCAT velho', 'vigencia_fim' => '2026-10-01'], $this->pdf(), $this->rh);

        $hoje = CarbonImmutable::parse('2026-11-05');
        $this->assertSame(['total' => 2, 'vencendo' => 1, 'vencidos' => 1], $svc->alertas(self::EMPRESA, $hoje));
        $this->assertSame(['PGR'], array_column($svc->listar(self::EMPRESA, ['situacao' => 'vencendo'], $this->rh, $hoje)['itens'], 'titulo'));
    }

    public function test_sigiloso_some_para_rh_e_abrir_audita_para_saude(): void
    {
        $saude = $this->usuario('Médica');
        $this->perfilSaude(true);
        $svc = app(DocumentosSstService::class);
        $laudo = $svc->criar(self::EMPRESA, ['tipo' => 'laudo_medico', 'titulo' => 'Laudo audiometria', 'sigilo' => 'comum', 'feedback_id' => $this->colaborador], $this->pdf(), $saude);
        $this->assertSame(SaudeDocumento::SIGILO_SENSIVEL, $laudo->sigilo, 'Tipo sigiloso nunca vira comum.');

        $versaoId = (int) DB::table('saude_documentos_versoes')->where('saude_documento_id', $laudo->id)->value('id');
        $aberto = $svc->abrir(self::EMPRESA, $versaoId, $saude, DocumentosSstService::ACESSO_VISUALIZAR);
        $this->assertSame('application/pdf', $aberto['mime']);
        $this->assertSame(1, DB::table('activity_log')->where('log_name', 'saude_ocupacional_acesso')->count());
        $this->assertSame(['enviar', 'visualizar'], DB::table('saude_documentos_acessos')->orderBy('id')->pluck('acao')->all());

        $this->perfilSaude(false);
        $this->assertSame(0, $svc->listar(self::EMPRESA, [], $this->rh)['total']);
        $this->assertSame(1, $svc->alertas(self::EMPRESA)['total'], 'O Painel conta o documento sem mostrar o conteúdo.');
        $this->expectException(ModelNotFoundException::class);
        $svc->abrir(self::EMPRESA, $versaoId, $this->rh, DocumentosSstService::ACESSO_BAIXAR);
    }

    public function test_rh_nao_envia_sigiloso(): void
    {
        $this->expectException(ValidationException::class);
        app(DocumentosSstService::class)->criar(self::EMPRESA, ['tipo' => 'ltcat', 'titulo' => 'X', 'sigilo' => 'sensivel'], $this->pdf(), $this->rh);
    }

    public function test_isolamento_entre_empresas_no_download(): void
    {
        $outraRh = $this->usuario('RH outra', self::OUTRA);
        $doc = app(DocumentosSstService::class)->criar(self::OUTRA, ['tipo' => 'pgr', 'titulo' => 'PGR outra'], $this->pdf(), $outraRh);
        $versaoId = (int) DB::table('saude_documentos_versoes')->where('saude_documento_id', $doc->id)->value('id');

        $this->expectException(ModelNotFoundException::class);
        app(DocumentosSstService::class)->abrir(self::EMPRESA, $versaoId, $this->rh, DocumentosSstService::ACESSO_BAIXAR);
    }

    public function test_recusa_arquivo_disfarcado_e_vigencia_invertida(): void
    {
        $svc = app(DocumentosSstService::class);
        $falso = UploadedFile::fake()->createWithContent('laudo.pdf', '<?php echo 1;');
        try {
            $svc->criar(self::EMPRESA, ['tipo' => 'ltcat', 'titulo' => 'X'], $falso, $this->rh);
            $this->fail('Arquivo com extensão .pdf e conteúdo de script deveria ser recusado.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('arquivo', $e->errors());
        }

        try {
            $svc->criar(self::EMPRESA, ['tipo' => 'ltcat', 'titulo' => 'X', 'vigencia_inicio' => '2026-12-01', 'vigencia_fim' => '2026-11-01'], $this->pdf(), $this->rh);
            $this->fail('Fim antes do início deveria ser recusado.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('vigencia_fim', $e->errors());
        }

        $this->assertSame(0, SaudeDocumento::query()->count());
        $this->assertSame([], Storage::disk(Arquivo::DISCO_SAUDE_DOCUMENTOS)->allFiles(), 'Nada fica gravado quando a validação falha.');
    }

    public function test_arquivar_mantem_arquivo_e_tira_da_lista(): void
    {
        $svc = app(DocumentosSstService::class);
        $doc = $svc->criar(self::EMPRESA, ['tipo' => 'pgr', 'titulo' => 'PGR'], $this->pdf(), $this->rh);
        $svc->arquivar(self::EMPRESA, $doc->id, $this->rh);

        $this->assertSame(0, $svc->listar(self::EMPRESA, [], $this->rh)['total']);
        $this->assertCount(1, Storage::disk(Arquivo::DISCO_SAUDE_DOCUMENTOS)->allFiles());
        $this->assertNotNull(SaudeDocumento::withTrashed()->find($doc->id)->deleted_at);
    }

    public function test_aso_assinado_entra_no_repositorio_do_colaborador_uma_vez(): void
    {
        Schema::dropIfExists('documento_para_assinatura');
        Schema::create('documento_para_assinatura', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->string('tipo_documento')->nullable();
            $t->string('documentable_type')->nullable();
            $t->unsignedBigInteger('documentable_id')->nullable();
            $t->unsignedBigInteger('arquivo_id')->nullable();
            $t->unsignedBigInteger('arquivo_assinado_id')->nullable();
            $t->string('hash_sha256')->nullable();
            $t->string('status')->nullable();
        });

        $tipo = DB::table('exame_tipos')->insertGetId(['label' => 'Periódico']);
        $ef = DB::table('exame_funcionarios')->insertGetId(['empresa_id' => self::EMPRESA, 'exame_tipo_id' => $tipo, 'feedback_id' => $this->colaborador, 'encaminhamento_data' => '2026-10-01']);
        $resultado = DB::table('examesesmts')->insertGetId(['empresa_id' => self::EMPRESA, 'exame_funcionario_id' => $ef, 'feedback_id' => $this->colaborador, 'exame_realizado' => true, 'data_realizacao' => '2026-10-02']);
        $assinado = DB::table('arquivos')->insertGetId(['nome' => 'aso (assinado)', 'extensao' => '.pdf', 'file' => '2026/10/aso.pdf', 'disco' => Arquivo::DISCO_DOCUMENTO_ASSINATURA]);
        $docId = DB::table('documento_para_assinatura')->insertGetId([
            'empresa_id' => self::EMPRESA, 'tipo_documento' => 'aso', 'documentable_type' => Examesesmt::class, 'documentable_id' => $resultado,
            'arquivo_assinado_id' => $assinado, 'hash_sha256' => str_repeat('a', 64), 'status' => DocumentoParaAssinatura::STATUS_CONCLUIDO,
        ]);
        ExamesesmtAso::query()->create([
            'examesesmt_id' => $resultado, 'empresa_id' => self::EMPRESA, 'examinador' => ['nome' => 'Dra. Teste', 'crm' => '1', 'crm_uf' => 'SE'],
            'riscos' => [], 'exames' => [], 'documento_assinatura_id' => $docId, 'emitido_em' => now(),
        ]);

        $doc = DocumentoParaAssinatura::withoutGlobalScopes()->with(['arquivo', 'arquivoAssinado'])->find($docId);
        $svc = app(ArquivamentoAutomaticoSst::class);
        $svc->arquivarAsoAssinado($doc);
        $svc->arquivarAsoAssinado($doc);

        $documento = SaudeDocumento::query()->sole();
        $this->assertSame('aso', $documento->tipo);
        $this->assertSame('ASO Periódico de 02/10/2026', $documento->titulo);
        $this->assertSame($this->colaborador, $documento->feedback_id);
        $versao = DB::table('saude_documentos_versoes')->sole();
        $this->assertSame($assinado, (int) $versao->arquivo_id, 'Reaproveita o PDF assinado, sem copiar.');
        $this->assertSame($docId, (int) $versao->documento_assinatura_id);
        $this->assertNull($versao->vigencia_fim, 'Vencimento do ASO fica na Conformidade.');

        $lista = app(DocumentosSstService::class)->listar(self::EMPRESA, ['feedback_id' => $this->colaborador], $this->rh);
        $this->assertSame('Maria Teste', $lista['itens'][0]['colaborador']);
    }

    public function test_pcmso_publicado_vira_documento_e_versao_anterior_recebe_fim(): void
    {
        $pdf = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('output')->andReturn("%PDF-1.4\npcmso\n%%EOF");
        $gerador = \Mockery::mock(\App\Domain\SaudeOcupacional\Pcmso\PcmsoPdfService::class);
        $gerador->shouldReceive('gerar')->andReturn($pdf);
        $this->app->instance(\App\Domain\SaudeOcupacional\Pcmso\PcmsoPdfService::class, $gerador);

        $pcmso = DB::table('pcmsos')->insertGetId(['empresa_id' => self::EMPRESA, 'label' => 'Matriz']);
        $v1 = DB::table('pcmsos_versoes')->insertGetId(['pcmso_id' => $pcmso, 'empresa_id' => self::EMPRESA, 'versao' => 1, 'status' => 'vigente', 'vigencia_inicio' => '2025-01-01', 'publicado_em' => '2025-01-01 10:00:00']);
        $svc = app(ArquivamentoAutomaticoSst::class);
        $this->assertNotNull($svc->arquivarPcmso(self::EMPRESA, $v1, $this->rh->id));
        $this->assertNull($svc->arquivarPcmso(self::EMPRESA, $v1, $this->rh->id), 'Não duplica.');

        // Publicou a versão 2: a 1 foi arquivada com fim = início da 2 − 1 dia.
        DB::table('pcmsos_versoes')->where('id', $v1)->update(['status' => 'arquivado', 'vigencia_fim' => '2026-10-31']);
        $v2 = DB::table('pcmsos_versoes')->insertGetId(['pcmso_id' => $pcmso, 'empresa_id' => self::EMPRESA, 'versao' => 2, 'status' => 'vigente', 'vigencia_inicio' => '2026-11-01', 'publicado_em' => '2026-11-01 10:00:00']);
        $doc2 = $svc->arquivarPcmso(self::EMPRESA, $v2, $this->rh->id);

        $this->assertSame('PCMSO Matriz - versão 2', $doc2->titulo);
        $lista = collect(app(DocumentosSstService::class)->listar(self::EMPRESA, [], $this->rh, CarbonImmutable::parse('2026-11-05'))['itens'])->keyBy('titulo');
        $this->assertSame('31/10/2026', $lista['PCMSO Matriz - versão 1']['vigencia_fim_br']);
        $this->assertSame(SituacaoDocumentoSst::VENCIDO, $lista['PCMSO Matriz - versão 1']['situacao']);
        $this->assertSame(SituacaoDocumentoSst::SEM_VALIDADE, $lista['PCMSO Matriz - versão 2']['situacao']);
        $this->assertTrue($lista['PCMSO Matriz - versão 2']['gerado_pelo_sistema']);
    }

    private function pdf(string $conteudo = 'base'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('documento.pdf', "%PDF-1.4\n" . $conteudo . "\n%%EOF");
    }

    private function usuario(string $nome, int $empresa = self::EMPRESA): User
    {
        $u = new User();
        $u->forceFill(['empresa_id' => $empresa, 'nome' => $nome, 'email' => strtolower(str_replace(' ', '', $nome)) . '@example.test'])->save();

        return $u;
    }

    private function perfilSaude(bool $saude): void
    {
        Gate::define('saude_ocupacional_dados_sensiveis', fn () => $saude);
        Gate::define('saude_ocupacional_configuracao', fn () => true);
    }
}
