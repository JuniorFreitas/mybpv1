<?php

namespace Tests\Unit\Domain\SaudeOcupacional;

use App\Domain\SaudeOcupacional\Painel\PainelCache;
use App\Domain\SaudeOcupacional\Painel\PainelSaudeOcupacionalService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PainelSaudeOcupacionalServiceTest extends TestCase
{
    private const EMPRESA = 10;

    private const OUTRA_EMPRESA = 20;

    private CarbonImmutable $hoje;

    private PainelSaudeOcupacionalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->criarTabelas();
        $this->hoje = CarbonImmutable::parse('2026-11-04');
        $this->service = new PainelSaudeOcupacionalService();
    }

    public function test_conta_status_de_aso_dos_colaboradores_ativos(): void
    {
        $this->configurarJanela(self::EMPRESA, 1); // 30 dias

        $vencido = $this->colaborador('Vencido');
        $this->aso($vencido, '2026-10-01');
        $this->aso($vencido, '2025-10-01', atual: false);

        $vencendo = $this->colaborador('Vencendo');
        $this->aso($vencendo, '2026-11-20');

        $emDia = $this->colaborador('Em dia');
        $this->aso($emDia, '2027-06-01');

        $this->colaborador('Sem ASO');

        $reprovado = $this->colaborador('Só ASO reprovado');
        $this->aso($reprovado, '2027-06-01', aprovado: 'Não');

        // Fora da população
        $demitido = $this->colaborador('Demitido');
        $this->aso($demitido, '2026-01-01');
        DB::table('demissaos')->insert(['feedback_id' => $demitido]);

        $excluido = $this->colaborador('Excluído', deletado: true);
        $this->aso($excluido, '2026-01-01');

        $emAndamento = $this->colaborador('Em andamento', status: 'EM ANDAMENTO');
        $this->aso($emAndamento, '2026-01-01');

        $outraEmpresa = $this->colaborador('Outra empresa', empresa: self::OUTRA_EMPRESA);
        $this->aso($outraEmpresa, '2026-01-01', empresa: self::OUTRA_EMPRESA);

        $resumo = $this->service->calcular(self::EMPRESA, $this->hoje);

        $this->assertSame(30, $resumo['janela_vencendo_dias']);
        $this->assertTrue($resumo['janela_configurada']);
        $this->assertSame([
            'total' => 5,
            'sem_aso' => 2,
            'vencidos' => 1,
            'vencendo' => 1,
            'em_dia' => 1,
        ], $resumo['aso']);
    }

    public function test_janela_padrao_quando_empresa_nao_configurou(): void
    {
        $resumo = $this->service->calcular(self::EMPRESA, $this->hoje);

        $this->assertSame(90, $resumo['janela_vencendo_dias']);
        $this->assertFalse($resumo['janela_configurada']);
    }

    public function test_afastamentos_em_curso_com_alertas_de_retorno_e_inss(): void
    {
        $longo = $this->colaborador('Longo voltando');
        $this->afastamento($longo, '2026-10-01', '2026-11-06'); // 37 dias, volta em 3

        $trintaDias = $this->colaborador('Trinta dias');
        $this->afastamento($trintaDias, '2026-11-01', '2026-11-30'); // 30 dias

        $curto = $this->colaborador('Curto');
        $this->afastamento($curto, '2026-11-03', '2026-11-05'); // 3 dias, volta em 2

        $encerrado = $this->colaborador('Encerrado');
        $this->afastamento($encerrado, '2026-09-01', '2026-09-10');

        $demitido = $this->colaborador('Demitido');
        DB::table('demissaos')->insert(['feedback_id' => $demitido]);
        $this->afastamento($demitido, '2026-11-01', '2026-11-30');

        $outra = $this->colaborador('Outra', empresa: self::OUTRA_EMPRESA);
        $this->afastamento($outra, '2026-11-01', '2026-11-30');

        $afastamentos = $this->service->calcular(self::EMPRESA, $this->hoje)['afastamentos'];

        $this->assertSame(3, $afastamentos['afastados_hoje']);
        $this->assertSame(2, $afastamentos['voltando_em_breve']);
        $this->assertSame(2, $afastamentos['acima_limite_inss']);

        $porNome = collect($afastamentos['lista'])->keyBy('colaborador');
        $this->assertSame(['Curto', 'Longo voltando', 'Trinta dias'], $porNome->keys()->sort()->values()->all());
        $this->assertSame(37, $porNome['Longo voltando']['dias']);
        $this->assertTrue($porNome['Longo voltando']['exige_aso_retorno']);
        $this->assertTrue($porNome['Trinta dias']['exige_aso_retorno']);
        $this->assertFalse($porNome['Curto']['exige_aso_retorno']);
        $this->assertFalse($porNome['Curto']['acima_limite_inss']);
        $this->assertSame('06/11/2026', $porNome['Longo voltando']['data_fim_br']);

        // Motivo do afastamento nunca sai no Painel (pode conter dado de saúde).
        $this->assertArrayNotHasKey('motivo', $porNome['Curto']);
    }

    public function test_resumo_usa_cache_e_invalidar_recalcula(): void
    {
        $id = $this->colaborador('Sem ASO');

        $this->assertSame(1, $this->service->resumo(self::EMPRESA, $this->hoje)['aso']['sem_aso']);

        $this->aso($id, '2027-06-01');
        $this->assertSame(1, $this->service->resumo(self::EMPRESA, $this->hoje)['aso']['sem_aso'], 'deveria vir do cache');

        PainelCache::invalidar(self::EMPRESA);
        $this->assertSame(0, $this->service->resumo(self::EMPRESA, $this->hoje)['aso']['sem_aso']);
    }

    private function colaborador(
        string $nome,
        int $empresa = self::EMPRESA,
        string $status = 'ADMITIDO',
        bool $deletado = false
    ): int {
        $curriculoId = DB::table('curriculos')->insertGetId(['nome' => $nome]);
        $feedbackId = DB::table('feedback_curriculos')->insertGetId([
            'empresa_id' => $empresa,
            'curriculo_id' => $curriculoId,
            'deleted_at' => $deletado ? '2026-01-01 00:00:00' : null,
        ]);
        DB::table('admissoes')->insert(['feedback_id' => $feedbackId, 'status' => $status]);

        return $feedbackId;
    }

    private function aso(int $feedbackId, string $vencimento, bool $atual = true, string $aprovado = 'Sim', int $empresa = self::EMPRESA): void
    {
        DB::table('examesesmts')->insert([
            'feedback_id' => $feedbackId,
            'empresa_id' => $empresa,
            'exame_realizado' => true,
            'atual' => $atual,
            'resultado' => json_encode(['aprovado' => $aprovado]),
            'data_realizacao' => '2025-01-01',
            'data_vencimento' => $vencimento,
        ]);
    }

    private function afastamento(int $feedbackId, string $inicio, string $fim): void
    {
        DB::table('afastamentos')->insert([
            'feedback_id' => $feedbackId,
            'motivo' => 'texto livre',
            'data_inicio' => $inicio,
            'data_fim' => $fim,
        ]);
    }

    private function configurarJanela(int $empresa, int $chave): void
    {
        DB::table('cliente_configs')->insert(['cliente_id' => $empresa, 'vencimento_aso' => $chave]);
    }

    private function criarTabelas(): void
    {
        foreach (['afastamentos', 'examesesmts', 'demissaos', 'admissoes', 'feedback_curriculos', 'curriculos', 'cliente_configs', 'vagas', 'vagas_abertas', 'exame_funcionarios'] as $tabela) {
            Schema::dropIfExists($tabela);
        }

        Schema::create('vagas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->boolean('ativo')->default(true);
            $table->unsignedBigInteger('empresa_id');
        });

        Schema::create('vagas_abertas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vaga_id');
            $table->unsignedBigInteger('empresa_id');
        });

        Schema::create('exame_funcionarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('feedback_id')->nullable();
            $table->date('encaminhamento_data')->nullable();
        });

        Schema::create('curriculos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->softDeletes();
        });

        Schema::create('feedback_curriculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('curriculo_id');
            $table->unsignedBigInteger('vaga_id')->nullable();
            $table->unsignedBigInteger('vagas_abertas_id')->nullable();
            $table->softDeletes();
        });

        Schema::create('admissoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->string('status');
            $table->string('cargo')->nullable();
            $table->softDeletes();
        });

        Schema::create('demissaos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
        });

        Schema::create('examesesmts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exame_funcionario_id')->nullable();
            $table->unsignedBigInteger('feedback_id')->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->boolean('exame_realizado');
            $table->boolean('atual')->default(true);
            $table->json('resultado');
            $table->date('data_realizacao');
            $table->date('data_vencimento')->nullable();
        });

        Schema::create('afastamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->text('motivo');
            $table->date('data_inicio');
            $table->date('data_fim');
        });

        Schema::create('cliente_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedInteger('vencimento_aso')->nullable();
            $table->boolean('modulo_saude_ocupacional')->default(false);
        });
    }
}
