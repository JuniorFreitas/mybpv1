<?php

namespace Tests\Unit\Domain\SaudeOcupacional\Parte2;

use App\Domain\SaudeOcupacional\Colaboradores\SaneamentoCargosService;
use App\Domain\SaudeOcupacional\Pgr\PgrInventarioService;
use App\Domain\SaudeOcupacional\Regras\RegrasEmpresaService;
use App\Domain\SaudeOcupacional\Regras\SimuladorVencimento;
use App\Jobs\ControleExames\JobRecalcularVencimentoAso;
use App\Models\PgrInventario;
use App\Models\User;
use App\Services\ControleExames\AsoPeriodicidadeService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Tests\Unit\Domain\SaudeOcupacional\CriaTabelasSaudeOcupacional;

class PgrExposicaoTest extends TestCase
{
    use CriaTabelasSaudeOcupacional;

    private const EMPRESA = 10;

    private const OUTRA = 20;

    private int $periodico;

    private int $operador;

    private int $administrativo;

    private int $ruido;

    private int $poeira;

    private User $autor;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->criarTabelasSaude();

        $this->periodico = DB::table('exame_tipos')->insertGetId(['label' => 'Periódico', 'ativo' => true]);
        DB::table('empresas_exame_tipos_periodicidades')->insert([
            'empresa_id' => self::EMPRESA, 'exame_tipo_id' => $this->periodico, 'unidade' => 'anos', 'valor' => 2,
        ]);
        DB::table('cliente_configs')->insert(['cliente_id' => self::EMPRESA, 'modulo_saude_ocupacional' => true]);

        $this->operador = DB::table('vagas')->insertGetId(['nome' => 'OPERADOR DE MÁQUINA', 'empresa_id' => self::EMPRESA]);
        $this->administrativo = DB::table('vagas')->insertGetId(['nome' => 'AUXILIAR ADMINISTRATIVO', 'empresa_id' => self::EMPRESA]);
        $fisico = DB::table('exame_risco_tipos')->insertGetId(['label' => 'Físico']);
        $quimico = DB::table('exame_risco_tipos')->insertGetId(['label' => 'Químico']);
        $this->ruido = DB::table('exame_riscos')->insertGetId(['empresa_id' => self::EMPRESA, 'risco_tipo' => $fisico, 'label' => 'Ruído']);
        $this->poeira = DB::table('exame_riscos')->insertGetId(['empresa_id' => self::EMPRESA, 'risco_tipo' => $quimico, 'label' => 'Poeira']);

        $this->autor = new User();
        $this->autor->forceFill(['empresa_id' => self::EMPRESA, 'nome' => 'SESMT', 'email' => 's@example.test'])->save();
    }

    public function test_exposto_no_pgr_vigente_tem_periodico_anual_automatico(): void
    {
        $exposto = $this->colaborador('Exposto', $this->operador);
        $escritorio = $this->colaborador('Escritório', $this->administrativo);
        $service = new AsoPeriodicidadeService();

        // Sem PGR: padrão de 2 anos para todos.
        $this->assertSame('09/01/2028', $service->calcularDataVencimento('10/01/2026', self::EMPRESA, $this->periodico, null, $exposto));

        $this->publicarPgr([$this->operador => [$this->ruido]], [$this->administrativo]);

        $this->assertSame('09/01/2027', $service->calcularDataVencimento('10/01/2026', self::EMPRESA, $this->periodico, null, $exposto));
        $this->assertSame('09/01/2028', $service->calcularDataVencimento('10/01/2026', self::EMPRESA, $this->periodico, null, $escritorio));
    }

    public function test_modulo_desligado_ignora_pgr(): void
    {
        $exposto = $this->colaborador('Exposto', $this->operador);
        $this->publicarPgr([$this->operador => [$this->ruido]]);
        DB::table('cliente_configs')->update(['modulo_saude_ocupacional' => false]);
        Cache::flush();

        $this->assertSame('09/01/2028', (new AsoPeriodicidadeService())->calcularDataVencimento('10/01/2026', self::EMPRESA, $this->periodico, null, $exposto));
    }

    public function test_regra_por_risco_especifico(): void
    {
        $ruido = $this->colaborador('Ruído', $this->operador);
        $this->publicarPgr([$this->operador => [$this->ruido]]);

        app(RegrasEmpresaService::class)->salvar(self::EMPRESA, null, [
            'nome' => 'Ruído semestral',
            'exame_tipo_ids' => [$this->periodico],
            'condicoes' => [['tipo' => 'risco', 'valor' => [$this->ruido]]],
            'unidade' => 'dias',
            'valor' => 180,
        ], 'futuro', null, $this->autor);

        $this->assertSame('09/07/2026', (new AsoPeriodicidadeService())->calcularDataVencimento('10/01/2026', self::EMPRESA, $this->periodico, null, $ruido));
    }

    public function test_regra_nao_aceita_risco_de_outra_empresa(): void
    {
        $outro = DB::table('exame_riscos')->insertGetId(['empresa_id' => self::OUTRA, 'risco_tipo' => 1, 'label' => 'X']);

        $this->expectException(ValidationException::class);
        app(RegrasEmpresaService::class)->salvar(self::EMPRESA, null, [
            'nome' => 'Inválida',
            'exame_tipo_ids' => [$this->periodico],
            'condicoes' => [['tipo' => 'risco', 'valor' => [$outro]]],
            'unidade' => 'anos',
            'valor' => 1,
        ], 'futuro', null, $this->autor);
    }

    public function test_rascunho_copia_vigente_e_so_um_por_vez(): void
    {
        $this->publicarPgr([$this->operador => [$this->ruido]]);
        $pgr = app(PgrInventarioService::class);

        $rascunho = $pgr->criarRascunho(self::EMPRESA, $this->autor);

        $this->assertSame(2, $rascunho->versao);
        $detalhe = $pgr->detalhe(self::EMPRESA, $rascunho->id);
        $operador = collect($detalhe['cargos'])->firstWhere('vaga_id', $this->operador);
        $this->assertSame('Ruído', $operador['riscos'][0]['label']);

        $this->expectException(ValidationException::class);
        $pgr->criarRascunho(self::EMPRESA, $this->autor);
    }

    public function test_salvar_cargo_valida_empresa_e_ausencia(): void
    {
        $pgr = app(PgrInventarioService::class);
        $rascunho = $pgr->criarRascunho(self::EMPRESA, $this->autor);
        $cargoOutra = DB::table('vagas')->insertGetId(['nome' => 'X', 'empresa_id' => self::OUTRA]);

        foreach ([
            fn () => $pgr->salvarCargo(self::EMPRESA, $rascunho->id, $cargoOutra, ['ausencia_risco' => true]),
            fn () => $pgr->salvarCargo(self::EMPRESA, $rascunho->id, $this->operador, ['ausencia_risco' => true, 'riscos' => [['exame_risco_id' => $this->ruido]]]),
        ] as $chamada) {
            try {
                $chamada();
                $this->fail('Deveria recusar.');
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }

        $pgr->salvarCargo(self::EMPRESA, $rascunho->id, $this->operador, ['ausencia_risco' => false, 'riscos' => [['exame_risco_id' => $this->ruido]]]);
        $this->assertSame(1, DB::table('pgr_inventarios_riscos')->count());

        // Sem riscos e sem ausência: cargo volta a "não avaliado".
        $pgr->salvarCargo(self::EMPRESA, $rascunho->id, $this->operador, ['ausencia_risco' => false, 'riscos' => []]);
        $this->assertSame(0, DB::table('pgr_inventarios_cargos')->count());
    }

    public function test_publicar_arquiva_anterior_e_dispara_recalculo_retroativo(): void
    {
        $this->publicarPgr([$this->operador => [$this->ruido]], [], '01/01/2026');
        $pgr = app(PgrInventarioService::class);
        $rascunho = $pgr->criarRascunho(self::EMPRESA, $this->autor);

        try {
            $pgr->publicar(self::EMPRESA, $rascunho->id, '01/12/2025', 'futuro', null, $this->autor);
            $this->fail('Início antes da vigente deveria ser recusado.');
        } catch (ValidationException) {
            $this->addToAssertionCount(1);
        }

        Queue::fake();
        $resultado = $pgr->publicar(self::EMPRESA, $rascunho->id, '01/06/2026', 'retroativo', null, $this->autor);

        $this->assertTrue($resultado['job_despachado']);
        Queue::assertPushed(JobRecalcularVencimentoAso::class);
        $versoes = PgrInventario::query()->orderBy('versao')->get();
        $this->assertSame([PgrInventario::STATUS_ARQUIVADO, PgrInventario::STATUS_VIGENTE], $versoes->pluck('status')->all());
        $this->assertSame('2026-05-31', $versoes[0]->vigencia_fim->toDateString());
        $this->assertSame(2, DB::table('activity_log')->where('log_name', 'saude_pgr')->where('description', 'like', 'publicou%')->count());
    }

    public function test_publicar_sem_cargo_avaliado_e_recusado(): void
    {
        $pgr = app(PgrInventarioService::class);
        $rascunho = $pgr->criarRascunho(self::EMPRESA, $this->autor);

        $this->expectException(ValidationException::class);
        $pgr->publicar(self::EMPRESA, $rascunho->id, '01/01/2026', 'futuro', null, $this->autor);
    }

    public function test_previa_da_publicacao_conta_asos_antecipados_e_cargos_sem_avaliacao(): void
    {
        $exposto = $this->colaborador('Exposto', $this->operador);
        $this->colaborador('Escritório', $this->administrativo);
        $this->aso($exposto, '2026-01-10', '2028-01-09');

        $pgr = app(PgrInventarioService::class);
        $rascunho = $pgr->criarRascunho(self::EMPRESA, $this->autor);
        $pgr->salvarCargo(self::EMPRESA, $rascunho->id, $this->operador, ['ausencia_risco' => false, 'riscos' => [['exame_risco_id' => $this->ruido]]]);

        $previa = $pgr->previaPublicacao(self::EMPRESA, $rascunho->id);

        $this->assertSame(1, $previa['alterados']);
        $this->assertSame(1, $previa['antecipados']);
        $this->assertSame('09/01/2027', $previa['exemplos'][0]['vencimento_novo_br']);
        $this->assertSame(1, $previa['cargos_sem_avaliacao'], 'auxiliar administrativo não avaliado');
    }

    public function test_simulador_mostra_riscos_e_regra_nr7(): void
    {
        $exposto = $this->colaborador('Exposto', $this->operador);
        $this->publicarPgr([$this->operador => [$this->ruido, $this->poeira]]);

        $resultado = app(SimuladorVencimento::class)->simular(self::EMPRESA, $exposto, CarbonImmutable::parse('2026-11-05'));

        $this->assertSame(['Poeira', 'Ruído'], collect($resultado['colaborador']['riscos'])->sort()->values()->all());
        $periodico = collect($resultado['tipos'])->firstWhere('exame_tipo_id', $this->periodico);
        $this->assertSame('Regra "NR-7: exposto a risco do PGR": 1 ano', $periodico['se_realizado_hoje']['motivo']);
    }

    public function test_saneamento_vincula_cargo_pelo_nome_da_admissao(): void
    {
        $semCargo = $this->colaborador('Sem cargo', null, '  operador   de máquina ');
        $semCorrespondente = $this->colaborador('Outro', null, 'SOLDADOR');
        $jaTem = $this->colaborador('Já tem', $this->administrativo, 'OPERADOR DE MÁQUINA');
        $saneamento = new SaneamentoCargosService();

        $analise = $saneamento->analisar(self::EMPRESA);
        $this->assertSame(2, $analise['sem_cargo']);
        $this->assertSame(1, $analise['casaveis']);
        $this->assertSame(['SOLDADOR' => 1], $analise['nao_casados']);

        $this->assertSame(1, $saneamento->aplicar(self::EMPRESA, $analise['mapa']));
        $this->assertSame($this->operador, (int) DB::table('feedback_curriculos')->where('id', $semCargo)->value('vaga_id'));
        $this->assertNull(DB::table('feedback_curriculos')->where('id', $semCorrespondente)->value('vaga_id'));
        $this->assertSame($this->administrativo, (int) DB::table('feedback_curriculos')->where('id', $jaTem)->value('vaga_id'));
    }

    /**
     * @param  array<int, list<int>>  $riscosPorCargo
     * @param  list<int>  $ausencia
     */
    private function publicarPgr(array $riscosPorCargo, array $ausencia = [], string $inicio = '01/01/2026'): void
    {
        $pgr = app(PgrInventarioService::class);
        $rascunho = $pgr->criarRascunho(self::EMPRESA, $this->autor);
        foreach ($riscosPorCargo as $vagaId => $riscos) {
            $pgr->salvarCargo(self::EMPRESA, $rascunho->id, $vagaId, [
                'ausencia_risco' => false,
                'riscos' => array_map(fn ($id) => ['exame_risco_id' => $id], $riscos),
            ]);
        }
        foreach ($ausencia as $vagaId) {
            $pgr->salvarCargo(self::EMPRESA, $rascunho->id, $vagaId, ['ausencia_risco' => true]);
        }
        $pgr->publicar(self::EMPRESA, $rascunho->id, $inicio, 'futuro', null, $this->autor);
    }

    private function colaborador(string $nome, ?int $vagaId, ?string $cargoAdmissao = null): int
    {
        $curriculo = DB::table('curriculos')->insertGetId(['nome' => $nome, 'nascimento' => '1990-01-01']);
        $id = DB::table('feedback_curriculos')->insertGetId(['empresa_id' => self::EMPRESA, 'curriculo_id' => $curriculo, 'vaga_id' => $vagaId]);
        DB::table('admissoes')->insert(['feedback_id' => $id, 'status' => 'ADMITIDO', 'cargo' => $cargoAdmissao]);

        return $id;
    }

    private function aso(int $feedbackId, string $realizacao, string $vencimento): void
    {
        $ef = DB::table('exame_funcionarios')->insertGetId(['empresa_id' => self::EMPRESA, 'exame_tipo_id' => $this->periodico, 'feedback_id' => $feedbackId]);
        DB::table('examesesmts')->insert([
            'empresa_id' => self::EMPRESA, 'exame_funcionario_id' => $ef, 'feedback_id' => $feedbackId,
            'exame_realizado' => true, 'atual' => true, 'data_realizacao' => $realizacao, 'data_vencimento' => $vencimento,
        ]);
    }
}
