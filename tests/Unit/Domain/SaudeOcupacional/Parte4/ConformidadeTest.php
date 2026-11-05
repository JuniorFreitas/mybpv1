<?php

namespace Tests\Unit\Domain\SaudeOcupacional\Parte4;

use App\Domain\SaudeOcupacional\Alertas\AlertasConfigService;
use App\Domain\SaudeOcupacional\Alertas\AlertasPeriodicosService;
use App\Domain\SaudeOcupacional\Conformidade\ConformidadeService;
use App\Domain\SaudeOcupacional\Conformidade\EncaminhamentoLoteService;
use App\Jobs\SaudeOcupacional\JobEncaminhamentoLote;
use App\Jobs\SaudeOcupacional\JobExportarConformidade;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Tests\Unit\Domain\SaudeOcupacional\CriaTabelasSaudeOcupacional;

class ConformidadeTest extends TestCase
{
    use CriaTabelasSaudeOcupacional;

    private const EMPRESA = 10;

    private const OUTRA = 20;

    private CarbonImmutable $hoje;

    private int $periodico;

    private int $admissional;

    private int $demissional;

    private int $operador;

    private ConformidadeService $conformidade;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->criarTabelasSaude();
        $this->hoje = CarbonImmutable::parse('2026-11-05');

        DB::table('cliente_configs')->insert(['cliente_id' => self::EMPRESA, 'modulo_saude_ocupacional' => true, 'vencimento_aso' => 1]); // 30 dias
        $this->admissional = DB::table('exame_tipos')->insertGetId(['label' => 'Admissional']);
        $this->periodico = DB::table('exame_tipos')->insertGetId(['label' => 'Periódico']);
        $this->demissional = DB::table('exame_tipos')->insertGetId(['label' => 'Demissional']);
        $this->operador = DB::table('vagas')->insertGetId(['nome' => 'OPERADOR', 'empresa_id' => self::EMPRESA]);
        $this->conformidade = app(ConformidadeService::class);
    }

    public function test_status_detalhes_e_totais(): void
    {
        $vencido = $this->colaborador('Ana Vencida', $this->operador);
        $this->aso($vencido, '2025-01-10', '2026-01-09', $this->periodico);
        $vencendo = $this->colaborador('Bruno Vencendo');
        $this->aso($vencendo, '2025-11-21', '2026-11-20', $this->admissional);
        $emDia = $this->colaborador('Carla Em Dia');
        $this->aso($emDia, '2026-06-01', '2027-05-31', $this->periodico);
        $semAso = $this->colaborador('Davi Sem ASO');
        $this->encaminhamento($semAso, '2026-11-01'); // sem resultado
        $this->colaborador('Outra empresa', null, self::OUTRA);

        $this->assertSame(['total' => 4, 'vencido' => 1, 'vencendo' => 1, 'sem_aso' => 1, 'em_dia' => 1], $this->conformidade->totais(self::EMPRESA, $this->hoje));

        $pagina = $this->conformidade->listar(self::EMPRESA, [], 1, 30, $this->hoje);
        $porNome = collect($pagina->items())->keyBy('colaborador');

        // Ordem: vencidos, sem ASO, vencendo, em dia.
        $this->assertSame(['Ana Vencida', 'Davi Sem ASO', 'Bruno Vencendo', 'Carla Em Dia'], collect($pagina->items())->pluck('colaborador')->all());
        $this->assertSame('OPERADOR', $porNome['Ana Vencida']['cargo']);
        $this->assertSame('Periódico', $porNome['Ana Vencida']['ultimo_tipo']);
        $this->assertSame(-300, $porNome['Ana Vencida']['dias']);
        $this->assertSame(15, $porNome['Bruno Vencendo']['dias']);
        $this->assertSame('01/11/2026', $porNome['Davi Sem ASO']['encaminhado_em_br']);
        $this->assertNull($porNome['Carla Em Dia']['encaminhado_em_br'], 'encaminhamento com resultado não conta');
        $this->assertSame('Padrão do tipo de exame: 1 ano', $porNome['Carla Em Dia']['motivo']);
    }

    public function test_filtros_de_status_busca_e_cargo(): void
    {
        $a = $this->colaborador('Ana Vencida', $this->operador);
        $this->aso($a, '2025-01-10', '2026-01-09', $this->periodico);
        $b = $this->colaborador('Bruno Vencido');
        $this->aso($b, '2025-01-10', '2026-01-09', $this->periodico);
        $this->colaborador('Carla Sem ASO', $this->operador);

        $this->assertSame(2, $this->conformidade->listar(self::EMPRESA, ['status' => 'vencido'], 1, 30, $this->hoje)->total());
        $this->assertSame(['Bruno Vencido'], collect($this->conformidade->listar(self::EMPRESA, ['busca' => 'brun'], 1, 30, $this->hoje)->items())->pluck('colaborador')->all());
        $this->assertSame(2, $this->conformidade->listar(self::EMPRESA, ['vaga_id' => $this->operador], 1, 30, $this->hoje)->total());
        $this->assertSame(1, $this->conformidade->totais(self::EMPRESA, $this->hoje, ['vaga_id' => $this->operador])['sem_aso']);
    }

    public function test_encaminhamento_em_lote_valida_e_evita_duplicidade(): void
    {
        Queue::fake();
        [$autor, $params] = $this->prepararEncaminhamento();
        $livre = $this->colaborador('Livre');
        $comAberto = $this->colaborador('Com aberto');
        $this->encaminhamento($comAberto, '2026-11-01');
        $outra = $this->colaborador('Outra', null, self::OUTRA);
        $lote = app(EncaminhamentoLoteService::class);

        $r = $lote->enfileirar(self::EMPRESA, $params + ['feedback_ids' => [$livre, $comAberto, $outra]], $autor);
        $this->assertSame(1, $r['enfileirados']);
        $this->assertSame(['com_encaminhamento_aberto' => 1, 'fora_da_lista_de_ativos' => 1, 'em_processamento' => 0], $r['ignorados']);
        Queue::assertPushed(JobEncaminhamentoLote::class, fn ($job) => $job->feedbackIds === [$livre]);

        // Segundo clique logo em seguida: trava em cache impede duplicar.
        $r2 = $lote->enfileirar(self::EMPRESA, $params + ['feedback_ids' => [$livre]], $autor);
        $this->assertSame(0, $r2['enfileirados']);
        $this->assertSame(1, $r2['ignorados']['em_processamento']);
    }

    public function test_encaminhamento_em_lote_recusa_demissional_e_clinica_de_outra_empresa(): void
    {
        [$autor, $params] = $this->prepararEncaminhamento();
        $id = $this->colaborador('X');
        $clinicaOutra = DB::table('empresa_exames')->insertGetId(['empresa_id' => self::OUTRA, 'nome' => 'Outra']);
        $lote = app(EncaminhamentoLoteService::class);

        foreach ([['exame_tipo_id' => $this->demissional], ['empresa_exame_id' => $clinicaOutra]] as $troca) {
            try {
                $lote->enfileirar(self::EMPRESA, array_merge($params, $troca, ['feedback_ids' => [$id]]), $autor);
                $this->fail('Deveria recusar.');
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_job_do_lote_cria_encaminhamentos_pelo_caminho_do_pcmso(): void
    {
        [$autor, $params] = $this->prepararEncaminhamento();
        $a = $this->colaborador('A');
        $b = $this->colaborador('B');
        $formulario = DB::table('formularios')->value('id');

        (new JobEncaminhamentoLote(self::EMPRESA, $autor->id, [$a, $b], [
            'exame_tipo_id' => $this->periodico,
            'empresa_exame_id' => $params['empresa_exame_id'],
            'pcmso_id' => $params['pcmso_id'],
            'formulario_id' => (int) $formulario,
            'data_realizacao' => '2026-11-12',
            'envia_email' => false,
            'envia_whatsapp' => false,
        ]))->handle(app(\App\Services\ControleExames\ExameEncaminhamentoNotificacaoService::class));

        $linhas = DB::table('exame_funcionarios')->whereIn('feedback_id', [$a, $b])->get();
        $this->assertCount(2, $linhas);
        $this->assertTrue((bool) $linhas[0]->pcmso);
        $this->assertSame($params['pcmso_id'], (int) $linhas[0]->pcmso_id);
        $this->assertSame($autor->id, (int) $linhas[0]->user_encaminhou_id);
        $this->assertSame('2026-11-12', substr((string) $linhas[0]->encaminhamento_data, 0, 10));
        $this->assertSame('12/11/2026', $this->conformidade->listar(self::EMPRESA, ['busca' => 'A'], 1, 30, $this->hoje)->items()[0]['encaminhado_em_br']);
    }

    public function test_exportacao_usa_nao_informado_e_neutraliza_formula(): void
    {
        $job = new JobExportarConformidade(self::EMPRESA, 1, [], 'x', 'k');

        $this->assertSame(
            ['Não informado', "'=CMD()", 'Sem ASO', 'Não informado', 'Não informado', 'Não informado', 'Não informado', 'Não informado'],
            $job->linha(['colaborador' => '', 'cargo' => '=CMD()', 'status' => 'sem_aso', 'dias' => null])
        );
    }

    public function test_alertas_por_marcos_avisam_uma_vez(): void
    {
        $em30 = $this->colaborador('Trinta dias');
        $this->aso($em30, '2025-12-06', '2026-12-05', $this->periodico);
        $venceuOntem = $this->colaborador('Venceu ontem');
        $this->aso($venceuOntem, '2025-11-05', '2026-11-04', $this->periodico);
        $em12 = $this->colaborador('Doze dias');
        $this->aso($em12, '2025-11-18', '2026-11-17', $this->periodico);
        $alertas = app(AlertasPeriodicosService::class);

        $itens = $alertas->marcosDoDia(self::EMPRESA, $this->hoje, [60, 30, 7]);
        $this->assertSame(['Venceu ontem', 'Trinta dias'], array_column($itens, 'colaborador'));
        $this->assertSame(['Venceu ontem', 'Vence em 30 dias'], array_column($itens, 'marco'));

        $this->assertSame([], $alertas->marcosDoDia(self::EMPRESA, $this->hoje, [60, 30, 7]), 'mesmo marco não repete');
    }

    public function test_config_de_alertas_valida_e_ordena(): void
    {
        $config = app(AlertasConfigService::class);

        $this->assertSame(['janelas' => [60, 30, 7], 'resumo_semanal' => true], $config->obter(self::EMPRESA));
        $this->assertSame(['janelas' => [45, 15, 5], 'resumo_semanal' => false], $config->salvar(self::EMPRESA, [5, 45, 15, 15], false));
        $this->assertSame([45, 15, 5], $config->obter(self::EMPRESA)['janelas']);

        $this->expectException(ValidationException::class);
        $config->salvar(self::EMPRESA, [0, 500], true);
    }

    /**
     * @return array{0: User, 1: array<string, mixed>}
     */
    private function prepararEncaminhamento(): array
    {
        $autor = new User();
        $autor->forceFill(['empresa_id' => self::EMPRESA, 'nome' => 'RH', 'email' => 'rh@example.test'])->save();
        DB::table('formularios')->insert(['empresa_id' => self::EMPRESA, 'titulo' => 'Exames']);

        return [$autor, [
            'exame_tipo_id' => $this->periodico,
            'empresa_exame_id' => DB::table('empresa_exames')->insertGetId(['empresa_id' => self::EMPRESA, 'nome' => 'Clínica']),
            'pcmso_id' => DB::table('pcmsos')->insertGetId(['empresa_id' => self::EMPRESA, 'label' => 'Fixo']),
            'data_realizacao' => CarbonImmutable::today()->addDays(7)->format('d/m/Y'),
            'envia_email' => false,
            'envia_whatsapp' => false,
        ]];
    }

    private function colaborador(string $nome, ?int $vagaId = null, int $empresa = self::EMPRESA): int
    {
        $curriculo = DB::table('curriculos')->insertGetId(['nome' => $nome, 'nascimento' => '1990-01-01']);
        $id = DB::table('feedback_curriculos')->insertGetId(['empresa_id' => $empresa, 'curriculo_id' => $curriculo, 'vaga_id' => $vagaId]);
        DB::table('admissoes')->insert(['feedback_id' => $id, 'status' => 'ADMITIDO']);

        return $id;
    }

    private function aso(int $feedbackId, string $realizacao, string $vencimento, int $tipo): void
    {
        $ef = DB::table('exame_funcionarios')->insertGetId([
            'empresa_id' => self::EMPRESA, 'exame_tipo_id' => $tipo, 'feedback_id' => $feedbackId, 'encaminhamento_data' => $realizacao,
        ]);
        DB::table('examesesmts')->insert([
            'empresa_id' => self::EMPRESA, 'exame_funcionario_id' => $ef, 'feedback_id' => $feedbackId,
            'exame_realizado' => true, 'atual' => true, 'resultado' => json_encode(['aprovado' => 'Sim']),
            'data_realizacao' => $realizacao, 'data_vencimento' => $vencimento,
        ]);
    }

    private function encaminhamento(int $feedbackId, string $data): void
    {
        DB::table('exame_funcionarios')->insert([
            'empresa_id' => self::EMPRESA, 'exame_tipo_id' => $this->periodico, 'feedback_id' => $feedbackId, 'encaminhamento_data' => $data,
        ]);
    }
}
