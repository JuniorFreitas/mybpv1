<?php

namespace Tests\Unit\Domain\SaudeOcupacional\Regras;

use App\Domain\SaudeOcupacional\Regras\PreviaImpactoRegra;
use App\Domain\SaudeOcupacional\Regras\RegrasEmpresaService;
use App\Domain\SaudeOcupacional\Regras\SimuladorVencimento;
use App\Jobs\ControleExames\JobRecalcularVencimentoAso;
use App\Jobs\ControleExames\JobRecalcularVencimentoAsoLote;
use App\Models\SaudeRegra;
use App\Models\SaudeRegraHistorico;
use App\Models\User;
use App\Services\ControleExames\AsoPeriodicidadeService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegrasEmpresaIntegracaoTest extends TestCase
{
    private const EMPRESA = 10;

    private const OUTRA = 20;

    private int $periodico;

    private int $demissional;

    private User $autor;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->criarTabelas();

        $this->periodico = DB::table('exame_tipos')->insertGetId(['label' => 'Periódico', 'ativo' => true]);
        $this->demissional = DB::table('exame_tipos')->insertGetId(['label' => 'Demissional', 'ativo' => true]);
        DB::table('empresas_exame_tipos_periodicidades')->insert([
            'empresa_id' => self::EMPRESA, 'exame_tipo_id' => $this->periodico, 'unidade' => 'anos', 'valor' => 2,
        ]);
        DB::table('cliente_configs')->insert([
            ['cliente_id' => self::EMPRESA, 'modulo_saude_ocupacional' => true],
            ['cliente_id' => self::OUTRA, 'modulo_saude_ocupacional' => true],
        ]);

        $this->autor = new User();
        $this->autor->forceFill(['empresa_id' => self::EMPRESA, 'nome' => 'Configurador', 'email' => 'c@example.test'])->save();
    }

    public function test_vencimento_do_aso_aplica_regra_de_idade_do_colaborador(): void
    {
        $velho = $this->colaborador('Velho', '1970-01-01');
        $jovem = $this->colaborador('Jovem', '2000-01-01');
        $this->salvarRegra40();

        $service = new AsoPeriodicidadeService();

        $this->assertSame('09/01/2027', $service->calcularDataVencimento('10/01/2026', self::EMPRESA, $this->periodico, null, $velho));
        $this->assertSame('09/01/2028', $service->calcularDataVencimento('10/01/2026', self::EMPRESA, $this->periodico, null, $jovem));
        $this->assertSame('09/01/2028', $service->calcularDataVencimento('10/01/2026', self::EMPRESA, $this->periodico), 'sem colaborador: padrão');
    }

    public function test_modulo_desligado_ignora_regras(): void
    {
        $velho = $this->colaborador('Velho', '1970-01-01');
        $this->salvarRegra40();
        DB::table('cliente_configs')->where('cliente_id', self::EMPRESA)->update(['modulo_saude_ocupacional' => false]);
        Cache::flush();

        $vencimento = (new AsoPeriodicidadeService())->calcularDataVencimento('10/01/2026', self::EMPRESA, $this->periodico, null, $velho);

        $this->assertSame('09/01/2028', $vencimento);
    }

    public function test_cargo_vigente_vem_da_mudanca_de_cargo(): void
    {
        $motorista = DB::table('vagas')->insertGetId(['nome' => 'MOTORISTA', 'ativo' => true, 'empresa_id' => self::EMPRESA]);
        $auxiliar = DB::table('vagas')->insertGetId(['nome' => 'AUXILIAR', 'ativo' => true, 'empresa_id' => self::EMPRESA]);
        $vagaAbertaMotorista = DB::table('vagas_abertas')->insertGetId(['vaga_id' => $motorista, 'empresa_id' => self::EMPRESA]);

        // Admitido como auxiliar, promovido a motorista (mudança aprovada troca vagas_abertas_id).
        $id = $this->colaborador('Promovido', '2000-01-01', vagaId: $auxiliar, vagaAbertaId: $vagaAbertaMotorista);

        $this->service()->salvar(self::EMPRESA, null, [
            'nome' => 'Motoristas anual',
            'exame_tipo_ids' => [$this->periodico],
            'condicoes' => [['tipo' => 'cargo', 'valor' => [$motorista]]],
            'unidade' => 'anos',
            'valor' => 1,
        ], 'futuro', null, $this->autor);

        $this->assertSame('09/01/2027', (new AsoPeriodicidadeService())->calcularDataVencimento('10/01/2026', self::EMPRESA, $this->periodico, null, $id));
    }

    public function test_salvar_grava_historico_auditoria_e_despacha_recalculo_retroativo(): void
    {
        Queue::fake();

        $resultado = $this->service()->salvar(self::EMPRESA, null, $this->dadosRegra40(), 'retroativo', null, $this->autor);

        $this->assertTrue($resultado['job_despachado']);
        $regra = SaudeRegra::query()->findOrFail($resultado['regra_id']);
        $this->assertSame([['tipo' => 'idade_minima', 'valor' => 40]], $regra->condicoes);

        $historico = SaudeRegraHistorico::query()->where('saude_regra_id', $regra->id)->firstOrFail();
        $this->assertSame('criar', $historico->acao);
        $this->assertNull($historico->antes);
        $this->assertSame(1, DB::table('activity_log')->where('log_name', 'saude_regras')->count());

        Queue::assertPushed(JobRecalcularVencimentoAso::class, function ($job) {
            return $job->empresaId === self::EMPRESA
                && $job->tipos === [['exame_tipo_id' => $this->periodico, 'unidade' => 'anos', 'valor' => 2]];
        });
    }

    public function test_modo_futuro_nao_despacha_recalculo(): void
    {
        Queue::fake();

        $this->assertFalse($this->service()->salvar(self::EMPRESA, null, $this->dadosRegra40(), 'futuro', null, $this->autor)['job_despachado']);
        Queue::assertNothingPushed();
    }

    #[DataProvider('dadosInvalidos')]
    public function test_rejeita_dados_invalidos(callable $alterar): void
    {
        $dados = $alterar($this->dadosRegra40(), $this);

        $this->expectException(ValidationException::class);
        $this->service()->salvar(self::EMPRESA, null, $dados, 'futuro', null, $this->autor);
    }

    public static function dadosInvalidos(): array
    {
        return [
            'acima de 2 anos' => [fn ($d) => ['unidade' => 'anos', 'valor' => 3] + $d],
            'acima de 730 dias' => [fn ($d) => ['unidade' => 'dias', 'valor' => 731] + $d],
            'demissional' => [fn ($d, $t) => ['exame_tipo_ids' => [$t->demissional]] + $d],
            'cargo de outra empresa' => [function ($d) {
                $cargoOutra = DB::table('vagas')->insertGetId(['nome' => 'X', 'ativo' => true, 'empresa_id' => self::OUTRA]);

                return ['condicoes' => [['tipo' => 'cargo', 'valor' => [$cargoOutra]]]] + $d;
            }],
        ];
    }

    public function test_nao_altera_regra_de_outra_empresa(): void
    {
        $outro = new User();
        $outro->forceFill(['empresa_id' => self::OUTRA, 'nome' => 'Outro', 'email' => 'o@example.test'])->save();
        $regraId = $this->service()->salvar(self::EMPRESA, null, $this->dadosRegra40(), 'futuro', null, $this->autor)['regra_id'];

        $this->expectException(ModelNotFoundException::class);
        $this->service()->excluir(self::OUTRA, $regraId, 'futuro', null, $outro);
    }

    public function test_excluir_remove_regra_do_calculo_e_registra_historico(): void
    {
        $velho = $this->colaborador('Velho', '1970-01-01');
        $regraId = $this->salvarRegra40();

        $this->service()->excluir(self::EMPRESA, $regraId, 'futuro', null, $this->autor);

        $this->assertSoftDeleted('saude_regras', ['id' => $regraId]);
        $this->assertSame('excluir', SaudeRegraHistorico::query()->latest('id')->value('acao'));
        $this->assertSame('09/01/2028', (new AsoPeriodicidadeService())->calcularDataVencimento('10/01/2026', self::EMPRESA, $this->periodico, null, $velho));
    }

    public function test_job_de_recalculo_aplica_regras_por_colaborador(): void
    {
        $velho = $this->colaborador('Velho', '1970-01-01');
        $jovem = $this->colaborador('Jovem', '2000-01-01');
        $asoVelho = $this->aso($velho, '2026-01-10', '2028-01-09');
        $asoJovem = $this->aso($jovem, '2026-01-10', '2028-01-09');
        $this->salvarRegra40();

        (new JobRecalcularVencimentoAsoLote(self::EMPRESA, $this->periodico, 'anos', 2))->handle();

        $this->assertSame('2027-01-09', DB::table('examesesmts')->where('id', $asoVelho)->value('data_vencimento'));
        $this->assertSame('2028-01-09', DB::table('examesesmts')->where('id', $asoJovem)->value('data_vencimento'));
    }

    public function test_previa_conta_asos_que_mudam(): void
    {
        $this->aso($this->colaborador('Velho', '1970-01-01'), '2026-01-10', '2028-01-09');
        $this->aso($this->colaborador('Jovem', '2000-01-01'), '2026-01-10', '2028-01-09');
        $this->aso($this->colaborador('Demitido', '1960-01-01', demitido: true), '2026-01-10', '2028-01-09');

        $service = $this->service();
        $proposta = $service->comoRegraPeriodicidade($service->normalizar(self::EMPRESA, $this->dadosRegra40()), null);
        $previa = app(PreviaImpactoRegra::class)->calcular(self::EMPRESA, $proposta, null);

        $this->assertSame(2, $previa['avaliados']);
        $this->assertSame(1, $previa['alterados']);
        $this->assertSame(1, $previa['antecipados']);
        $this->assertSame('Velho', $previa['exemplos'][0]['colaborador']);
        $this->assertSame('09/01/2027', $previa['exemplos'][0]['vencimento_novo_br']);
    }

    public function test_simulador_explica_e_isola_empresas(): void
    {
        $velho = $this->colaborador('Velho', '1970-01-01');
        $outraEmpresa = $this->colaborador('Outra', '1970-01-01', empresa: self::OUTRA);
        $this->salvarRegra40();
        $simulador = app(SimuladorVencimento::class);

        $this->assertSame([['feedback_id' => $velho, 'nome' => 'Velho', 'cargo' => null]], $simulador->buscar(self::EMPRESA, 'Vel'));
        $this->assertSame([], $simulador->buscar(self::EMPRESA, 'Out'));

        $resultado = $simulador->simular(self::EMPRESA, $velho, CarbonImmutable::parse('2026-11-05'));
        $periodico = collect($resultado['tipos'])->firstWhere('exame_tipo_id', $this->periodico);
        $this->assertSame('04/11/2027', $periodico['se_realizado_hoje']['vencimento_br']);
        $this->assertSame('Regra "Idade 40+ anual": 1 ano', $periodico['se_realizado_hoje']['motivo']);
        $this->assertTrue($periodico['regras'][0]['aplica']);

        $this->expectException(ModelNotFoundException::class);
        $simulador->simular(self::EMPRESA, $outraEmpresa);
    }

    private function service(): RegrasEmpresaService
    {
        return app(RegrasEmpresaService::class);
    }

    private function dadosRegra40(): array
    {
        return [
            'nome' => 'Idade 40+ anual',
            'exame_tipo_ids' => [$this->periodico],
            'condicoes' => [['tipo' => 'idade_minima', 'valor' => 40]],
            'unidade' => 'anos',
            'valor' => 1,
        ];
    }

    private function salvarRegra40(): int
    {
        return $this->service()->salvar(self::EMPRESA, null, $this->dadosRegra40(), 'futuro', null, $this->autor)['regra_id'];
    }

    private function colaborador(
        string $nome,
        string $nascimento,
        int $empresa = self::EMPRESA,
        ?int $vagaId = null,
        ?int $vagaAbertaId = null,
        bool $demitido = false
    ): int {
        $curriculo = DB::table('curriculos')->insertGetId(['nome' => $nome, 'nascimento' => $nascimento]);
        $id = DB::table('feedback_curriculos')->insertGetId([
            'empresa_id' => $empresa, 'curriculo_id' => $curriculo, 'vaga_id' => $vagaId, 'vagas_abertas_id' => $vagaAbertaId,
        ]);
        DB::table('admissoes')->insert(['feedback_id' => $id, 'status' => 'ADMITIDO']);
        if ($demitido) {
            DB::table('demissaos')->insert(['feedback_id' => $id]);
        }

        return $id;
    }

    private function aso(int $feedbackId, string $realizacao, string $vencimento): int
    {
        $ef = DB::table('exame_funcionarios')->insertGetId([
            'empresa_id' => self::EMPRESA, 'exame_tipo_id' => $this->periodico, 'feedback_id' => $feedbackId,
        ]);

        return DB::table('examesesmts')->insertGetId([
            'empresa_id' => self::EMPRESA,
            'exame_funcionario_id' => $ef,
            'feedback_id' => $feedbackId,
            'exame_realizado' => true,
            'atual' => true,
            'data_realizacao' => $realizacao,
            'data_vencimento' => $vencimento,
        ]);
    }

    private function criarTabelas(): void
    {
        $tabelas = [
            'saude_regras_historicos', 'saude_regras', 'activity_log', 'examesesmts', 'exame_funcionarios',
            'empresas_exame_tipos_periodicidades', 'exame_tipos', 'demissaos', 'admissoes', 'feedback_curriculos',
            'curriculos', 'vagas_abertas', 'vagas', 'cliente_configs', 'users',
        ];
        foreach ($tabelas as $tabela) {
            Schema::dropIfExists($tabela);
        }

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id')->nullable();
            $t->string('nome')->nullable();
            $t->string('email')->nullable();
            $t->string('tipo')->nullable();
            $t->boolean('ativo')->default(true);
            $t->boolean('require_password_reset')->default(false);
            $t->unsignedInteger('password_reset_days')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });
        Schema::create('cliente_configs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('cliente_id');
            $t->boolean('modulo_saude_ocupacional')->default(false);
            $t->unsignedInteger('vencimento_aso')->nullable();
        });
        Schema::create('vagas', function (Blueprint $t) {
            $t->id();
            $t->string('nome');
            $t->boolean('ativo')->default(true);
            $t->unsignedBigInteger('empresa_id');
        });
        Schema::create('vagas_abertas', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('vaga_id');
            $t->unsignedBigInteger('empresa_id');
        });
        Schema::create('curriculos', function (Blueprint $t) {
            $t->id();
            $t->string('nome');
            $t->date('nascimento')->nullable();
            $t->softDeletes();
        });
        Schema::create('feedback_curriculos', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->unsignedBigInteger('curriculo_id');
            $t->unsignedBigInteger('vaga_id')->nullable();
            $t->unsignedBigInteger('vagas_abertas_id')->nullable();
            $t->softDeletes();
        });
        Schema::create('admissoes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('feedback_id');
            $t->string('status');
            $t->string('cargo')->nullable();
            $t->softDeletes();
        });
        Schema::create('demissaos', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('feedback_id');
        });
        Schema::create('exame_tipos', function (Blueprint $t) {
            $t->id();
            $t->string('label');
            $t->boolean('ativo')->default(true);
            $t->timestamps();
        });
        Schema::create('empresas_exame_tipos_periodicidades', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->unsignedBigInteger('exame_tipo_id');
            $t->string('unidade', 10);
            $t->unsignedInteger('valor');
            $t->timestamps();
        });
        Schema::create('exame_funcionarios', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->unsignedBigInteger('exame_tipo_id')->nullable();
            $t->unsignedBigInteger('feedback_id')->nullable();
        });
        Schema::create('examesesmts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->unsignedBigInteger('exame_funcionario_id');
            $t->unsignedBigInteger('feedback_id')->nullable();
            $t->boolean('exame_realizado')->default(false);
            $t->date('data_realizacao')->nullable();
            $t->date('data_vencimento')->nullable();
            $t->boolean('vencido')->default(false);
            $t->boolean('atual')->default(false);
            $t->timestamps();
        });
        Schema::create('activity_log', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('log_name')->nullable();
            $t->text('description');
            $t->text('descricao');
            $t->nullableMorphs('subject', 'subject');
            $t->nullableMorphs('causer', 'causer');
            $t->json('properties')->nullable();
            $t->uuid('batch_uuid')->nullable();
            $t->string('event')->nullable();
            $t->timestamps();
        });

        (require database_path('migrations/2026_11_05_110000_create_saude_regras_table.php'))->up();
        (require database_path('migrations/2026_11_05_110001_create_saude_regras_historicos_table.php'))->up();
    }
}
