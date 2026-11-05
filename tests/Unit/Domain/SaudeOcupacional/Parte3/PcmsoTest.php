<?php

namespace Tests\Unit\Domain\SaudeOcupacional\Parte3;

use App\Domain\SaudeOcupacional\Medicos\MedicosResponsaveisService;
use App\Domain\SaudeOcupacional\Pcmso\ExamesCatalogoService;
use App\Domain\SaudeOcupacional\Pcmso\ExamesPrevistosPcmso;
use App\Domain\SaudeOcupacional\Pcmso\PcmsoService;
use App\Domain\SaudeOcupacional\Pgr\PgrInventarioService;
use App\Models\PcmsoVersao;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Tests\Unit\Domain\SaudeOcupacional\CriaTabelasSaudeOcupacional;

class PcmsoTest extends TestCase
{
    use CriaTabelasSaudeOcupacional;

    private const EMPRESA = 10;

    private const OUTRA = 20;

    private int $programa;

    private int $operador;

    private int $administrativo;

    private int $admissional;

    private int $periodico;

    private int $demissional;

    private int $audiometria;

    private int $espirometria;

    private int $ruido;

    private User $autor;

    private PcmsoService $pcmso;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->criarTabelasSaude();

        DB::table('cliente_configs')->insert(['cliente_id' => self::EMPRESA, 'modulo_saude_ocupacional' => true]);
        $this->admissional = DB::table('exame_tipos')->insertGetId(['label' => 'Admissional']);
        $this->periodico = DB::table('exame_tipos')->insertGetId(['label' => 'Periódico']);
        $this->demissional = DB::table('exame_tipos')->insertGetId(['label' => 'Demissional']);

        $this->programa = DB::table('pcmsos')->insertGetId(['empresa_id' => self::EMPRESA, 'label' => 'MONTISOL']);
        $this->operador = DB::table('vagas')->insertGetId(['nome' => 'OPERADOR', 'empresa_id' => self::EMPRESA]);
        $this->administrativo = DB::table('vagas')->insertGetId(['nome' => 'ADMINISTRATIVO', 'empresa_id' => self::EMPRESA]);
        $this->audiometria = DB::table('exames')->insertGetId(['empresa_id' => self::EMPRESA, 'label' => 'Audiometria']);
        $this->espirometria = DB::table('exames')->insertGetId(['empresa_id' => self::EMPRESA, 'label' => 'Espirometria']);
        $fisico = DB::table('exame_risco_tipos')->insertGetId(['label' => 'Físico']);
        $this->ruido = DB::table('exame_riscos')->insertGetId(['empresa_id' => self::EMPRESA, 'risco_tipo' => $fisico, 'label' => 'Ruído']);

        $this->autor = new User();
        $this->autor->forceFill(['empresa_id' => self::EMPRESA, 'nome' => 'SESMT', 'email' => 's@example.test'])->save();
        $this->pcmso = app(PcmsoService::class);
    }

    public function test_publicar_exige_coordenador_vigente(): void
    {
        $rascunho = $this->pcmso->criarRascunho(self::EMPRESA, $this->programa, $this->autor);
        $this->pcmso->salvarCargo(self::EMPRESA, $rascunho->id, $this->operador, [$this->linhaAudiometria()]);

        try {
            $this->pcmso->publicar(self::EMPRESA, $rascunho->id, '01/01/2026', null, $this->autor);
            $this->fail('Sem coordenador deveria recusar.');
        } catch (ValidationException) {
            $this->addToAssertionCount(1);
        }

        $this->pcmso->salvarCabecalho(self::EMPRESA, $rascunho->id, ['saude_medico_id' => $this->coordenador('01/01/2026')]);
        $publicada = $this->pcmso->publicar(self::EMPRESA, $rascunho->id, '01/01/2026', '31/12/2026', $this->autor);

        $this->assertSame(PcmsoVersao::STATUS_VIGENTE, $publicada->status);
        $this->assertSame('31/12/2026', $publicada->vigencia_fim_br);
        $this->assertSame(1, DB::table('activity_log')->where('log_name', 'saude_pcmso')->where('description', 'like', 'publicou%')->count());
    }

    public function test_coordenador_com_vinculo_comecando_depois_do_inicio_e_recusado(): void
    {
        $rascunho = $this->pcmso->criarRascunho(self::EMPRESA, $this->programa, $this->autor);
        $this->pcmso->salvarCargo(self::EMPRESA, $rascunho->id, $this->operador, [$this->linhaAudiometria()]);
        $this->pcmso->salvarCabecalho(self::EMPRESA, $rascunho->id, ['saude_medico_id' => $this->coordenador('01/03/2026')]);

        $this->expectException(ValidationException::class);
        $this->pcmso->publicar(self::EMPRESA, $rascunho->id, '01/01/2026', null, $this->autor);
    }

    public function test_nova_versao_copia_a_vigente_e_publicar_arquiva_a_anterior(): void
    {
        $this->publicarV1();

        $v2 = $this->pcmso->criarRascunho(self::EMPRESA, $this->programa, $this->autor);
        $this->assertSame(2, $v2->versao);
        $operador = collect($this->pcmso->detalhe(self::EMPRESA, $v2->id)['cargos'])->firstWhere('vaga_id', $this->operador);
        $this->assertSame('Audiometria', $operador['exames'][0]['label']);

        $this->pcmso->publicar(self::EMPRESA, $v2->id, '01/06/2026', null, $this->autor);

        $versoes = PcmsoVersao::query()->orderBy('versao')->get();
        $this->assertSame([PcmsoVersao::STATUS_ARQUIVADO, PcmsoVersao::STATUS_VIGENTE], $versoes->pluck('status')->all());
        $this->assertSame('2026-05-31', $versoes[0]->vigencia_fim->toDateString());
    }

    public function test_um_rascunho_por_programa(): void
    {
        $this->pcmso->criarRascunho(self::EMPRESA, $this->programa, $this->autor);

        $this->expectException(ValidationException::class);
        $this->pcmso->criarRascunho(self::EMPRESA, $this->programa, $this->autor);
    }

    public function test_salvar_cargo_valida_tipos_periodicidade_e_empresa(): void
    {
        $rascunho = $this->pcmso->criarRascunho(self::EMPRESA, $this->programa, $this->autor);
        $exameOutra = DB::table('exames')->insertGetId(['empresa_id' => self::OUTRA, 'label' => 'X']);

        foreach ([
            [['exame_id' => $this->audiometria, 'exame_tipo_ids' => []]],
            [['exame_id' => $this->audiometria, 'exame_tipo_ids' => [$this->periodico], 'unidade' => 'anos', 'valor' => 3]],
            [['exame_id' => $exameOutra, 'exame_tipo_ids' => [$this->periodico]]],
        ] as $itens) {
            try {
                $this->pcmso->salvarCargo(self::EMPRESA, $rascunho->id, $this->operador, $itens);
                $this->fail('Deveria recusar.');
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }

        $this->pcmso->salvarCargo(self::EMPRESA, $rascunho->id, $this->operador, [
            ['exame_id' => $this->audiometria, 'exame_tipo_ids' => [$this->periodico], 'unidade' => 'dias', 'valor' => 180],
        ]);
        $detalhe = collect($this->pcmso->detalhe(self::EMPRESA, $rascunho->id)['cargos'])->firstWhere('vaga_id', $this->operador);
        $this->assertSame('180 dias', $detalhe['exames'][0]['periodicidade']);
    }

    public function test_sugerir_pelo_pgr_preenche_cargos_expostos_sem_duplicar(): void
    {
        $this->publicarPgr();
        app(ExamesCatalogoService::class)->salvarExamesDoRisco(self::EMPRESA, $this->ruido, [
            ['exame_id' => $this->audiometria, 'exame_tipo_ids' => [$this->admissional, $this->periodico]],
        ], [$this->admissional, $this->periodico, $this->demissional]);

        $rascunho = $this->pcmso->criarRascunho(self::EMPRESA, $this->programa, $this->autor);

        $this->assertSame(['cargos' => 1, 'exames_adicionados' => 1], $this->pcmso->sugerirPeloPgr(self::EMPRESA, $rascunho->id));
        $this->assertSame(['cargos' => 0, 'exames_adicionados' => 0], $this->pcmso->sugerirPeloPgr(self::EMPRESA, $rascunho->id));

        $linha = DB::table('pcmsos_versoes_exames')->first();
        $this->assertSame($this->operador, (int) $linha->vaga_id);
        $this->assertSame('sugerido', $linha->origem);
        $this->assertNull(DB::table('pcmsos_versoes_exames')->where('vaga_id', $this->administrativo)->first());
    }

    public function test_sugerir_sem_pgr_publicado_e_recusado(): void
    {
        $rascunho = $this->pcmso->criarRascunho(self::EMPRESA, $this->programa, $this->autor);

        $this->expectException(ValidationException::class);
        $this->pcmso->sugerirPeloPgr(self::EMPRESA, $rascunho->id);
    }

    public function test_exames_previstos_para_a_ficha_da_clinica(): void
    {
        $this->publicarV1([
            ['exame_id' => $this->audiometria, 'exame_tipo_ids' => [$this->admissional, $this->periodico]],
            ['exame_id' => $this->espirometria, 'exame_tipo_ids' => [$this->periodico], 'obrigatorio' => false, 'unidade' => 'anos', 'valor' => 2],
        ]);
        $colaborador = $this->colaborador($this->operador);
        $escritorio = $this->colaborador($this->administrativo);
        $previstos = app(ExamesPrevistosPcmso::class);
        $data = CarbonImmutable::parse('2026-03-01');

        $periodico = $previstos->para(self::EMPRESA, $this->programa, $colaborador, $this->periodico, $data);
        $this->assertSame(1, $periodico['versao']);
        $this->assertSame([
            ['label' => 'Audiometria', 'obrigatorio' => true, 'periodicidade' => null],
            ['label' => 'Espirometria', 'obrigatorio' => false, 'periodicidade' => '2 anos'],
        ], $periodico['exames']);

        $this->assertSame(['Audiometria'], array_column($previstos->para(self::EMPRESA, $this->programa, $colaborador, $this->admissional, $data)['exames'], 'label'));
        $this->assertSame([], $previstos->para(self::EMPRESA, $this->programa, $escritorio, $this->periodico, $data)['exames']);
        $this->assertNull($previstos->para(self::EMPRESA, $this->programa, $colaborador, $this->periodico, CarbonImmutable::parse('2025-12-01')), 'antes da vigência');

        DB::table('cliente_configs')->update(['modulo_saude_ocupacional' => false]);
        Cache::flush();
        $this->assertNull($previstos->para(self::EMPRESA, $this->programa, $colaborador, $this->periodico, $data), 'módulo desligado');
    }

    public function test_alertas_contam_programas_sem_versao_e_vencendo(): void
    {
        DB::table('pcmsos')->insert(['empresa_id' => self::EMPRESA, 'label' => 'SEM VERSÃO']);
        $this->publicarV1(null, '01/01/2026', '20/11/2026');

        $alertas = $this->pcmso->alertas(self::EMPRESA, CarbonImmutable::parse('2026-11-05'));

        $this->assertSame(2, $alertas['programas_ativos']);
        $this->assertSame(1, $alertas['sem_vigente']);
        $this->assertSame(1, $alertas['vencendo']);
        $this->assertSame(0, $alertas['vencidos']);
    }

    public function test_isolamento_entre_empresas(): void
    {
        $rascunho = $this->pcmso->criarRascunho(self::EMPRESA, $this->programa, $this->autor);

        $this->expectException(ModelNotFoundException::class);
        $this->pcmso->detalhe(self::OUTRA, $rascunho->id);
    }

    public function test_pdf_do_pcmso_renderiza_cargos_e_coordenador(): void
    {
        $html = view('pdf.saude-ocupacional.pcmso', ['dados' => [
            'dados_empresa' => ['razao_social' => 'EMPRESA TESTE LTDA', 'cnpj' => '00.000.000/0001-00'],
            'programa' => 'MONTISOL',
            'versao' => ['versao' => 1, 'status' => 'vigente', 'vigencia_inicio_br' => '01/01/2026', 'vigencia_fim_br' => null, 'observacao' => null],
            'coordenador' => (object) ['nome' => 'Dra. Teste', 'crm' => '12345', 'crm_uf' => 'SP', 'rqe' => null],
            'pgr_vigente' => ['versao' => 1],
            'cargos' => [[
                'nome' => 'OPERADOR',
                'riscos' => ['Ruído'],
                'exames' => [['label' => 'Audiometria', 'obrigatorio' => true, 'tipos' => ['Admissional', 'Periódico'], 'periodicidade' => null]],
            ]],
            'data_emissao' => '05/11/2026',
        ]])->render();

        $this->assertStringContainsString('Programa de Controle Médico de Saúde Ocupacional', $html);
        $this->assertStringContainsString('Audiometria', $html);
        $this->assertStringContainsString('CRM 12345/SP', $html);
    }

    private function linhaAudiometria(): array
    {
        return ['exame_id' => $this->audiometria, 'exame_tipo_ids' => [$this->admissional, $this->periodico]];
    }

    private function coordenador(string $inicio): int
    {
        return app(MedicosResponsaveisService::class)->salvar(self::EMPRESA, null, [
            'nome' => 'Dra. Teste', 'crm' => (string) random_int(10000, 99999), 'crm_uf' => 'SP',
            'vinculos' => [['papel' => 'coordenador', 'vigencia_inicio' => $inicio]],
        ], $this->autor)->id;
    }

    private function publicarV1(?array $exames = null, string $inicio = '01/01/2026', ?string $fim = null): void
    {
        $rascunho = $this->pcmso->criarRascunho(self::EMPRESA, $this->programa, $this->autor);
        $this->pcmso->salvarCargo(self::EMPRESA, $rascunho->id, $this->operador, $exames ?? [$this->linhaAudiometria()]);
        $this->pcmso->salvarCabecalho(self::EMPRESA, $rascunho->id, ['saude_medico_id' => $this->coordenador('01/01/2025')]);
        $this->pcmso->publicar(self::EMPRESA, $rascunho->id, $inicio, $fim, $this->autor);
    }

    private function publicarPgr(): void
    {
        $pgr = app(PgrInventarioService::class);
        $rascunho = $pgr->criarRascunho(self::EMPRESA, $this->autor);
        $pgr->salvarCargo(self::EMPRESA, $rascunho->id, $this->operador, ['ausencia_risco' => false, 'riscos' => [['exame_risco_id' => $this->ruido]]]);
        $pgr->salvarCargo(self::EMPRESA, $rascunho->id, $this->administrativo, ['ausencia_risco' => true]);
        $pgr->publicar(self::EMPRESA, $rascunho->id, '01/01/2026', 'futuro', null, $this->autor);
    }

    private function colaborador(int $vagaId): int
    {
        $curriculo = DB::table('curriculos')->insertGetId(['nome' => 'Colaborador', 'nascimento' => '1990-01-01']);
        $id = DB::table('feedback_curriculos')->insertGetId(['empresa_id' => self::EMPRESA, 'curriculo_id' => $curriculo, 'vaga_id' => $vagaId]);
        DB::table('admissoes')->insert(['feedback_id' => $id, 'status' => 'ADMITIDO']);

        return $id;
    }
}
