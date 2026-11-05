<?php

namespace Tests\Unit\Services\ControleExames;

use App\Jobs\ControleExames\JobRecalcularVencimentoAso;
use App\Jobs\ControleExames\JobRecalcularVencimentoAsoLote;
use App\Models\EmpresaExameTipoPeriodicidade;
use App\Models\EmpresaExameTipoPeriodicidadeHistorico;
use App\Models\ExameTipo;
use App\Models\User;
use App\Services\ControleExames\AsoPeriodicidadeService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AsoPeriodicidadeServiceTest extends TestCase
{
    private AsoPeriodicidadeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->criarTabelas();
        $this->service = new AsoPeriodicidadeService();
        Cache::flush();
    }

    public function test_default_um_ano_menos_um_dia(): void
    {
        $vencimento = $this->service->calcularDataVencimento('15/03/2024', 10, null);

        $this->assertSame('14/03/2025', $vencimento);
    }

    public function test_demissional_nao_tem_vencimento(): void
    {
        $tipo = $this->criarTipo('Demissional');

        $this->assertTrue($this->service->ehSemVencimento((int) $tipo->id));
        $this->assertNull(
            $this->service->calcularDataVencimento('15/03/2024', 10, (int) $tipo->id)
        );

        $lista = $this->service->listarParaEmpresa(10);
        $demissional = collect($lista)->firstWhere('exame_tipo_id', $tipo->id);
        $this->assertTrue($demissional['sem_vencimento']);
        $this->assertNull($demissional['unidade']);
        $this->assertNull($demissional['valor']);
    }

    public function test_salvar_ignora_demissional(): void
    {
        Queue::fake();
        $user = $this->criarUsuario(10);
        $demissional = $this->criarTipo('Demissional');
        $periodico = $this->criarTipo('Periódico');

        EmpresaExameTipoPeriodicidade::withoutGlobalScopes()->create([
            'empresa_id' => 10,
            'exame_tipo_id' => $demissional->id,
            'unidade' => 'anos',
            'valor' => 1,
        ]);

        $resultado = $this->service->salvar(
            10,
            [
                ['exame_tipo_id' => $demissional->id, 'unidade' => 'anos', 'valor' => 1],
                ['exame_tipo_id' => $periodico->id, 'unidade' => 'anos', 'valor' => 2],
            ],
            AsoPeriodicidadeService::MODO_FUTURO,
            null,
            $user
        );

        $this->assertFalse(
            EmpresaExameTipoPeriodicidade::withoutGlobalScopes()
                ->where('empresa_id', 10)
                ->where('exame_tipo_id', $demissional->id)
                ->exists()
        );
        $this->assertTrue(
            collect($resultado['tipos_alterados'])->every(
                fn ($alt) => (int) $alt['exame_tipo_id'] !== (int) $demissional->id
            )
        );
    }

    public function test_dois_anos_menos_um_dia(): void
    {
        $vencimento = $this->service->calcularDataVencimento(
            '15/03/2024',
            10,
            2,
            ['unidade' => 'anos', 'valor' => 2]
        );

        $this->assertSame('14/03/2026', $vencimento);
    }

    public function test_dias_custom_sem_menos_um(): void
    {
        $vencimento = $this->service->calcularDataVencimento(
            '01/01/2024',
            10,
            3,
            ['unidade' => 'dias', 'valor' => 10]
        );

        $this->assertSame('11/01/2024', $vencimento);
    }

    public function test_futuro_nao_despacha_job_e_grava_historico(): void
    {
        Queue::fake();
        $user = $this->criarUsuario(10);
        $tipo = $this->criarTipo('Periódico');

        $resultado = $this->service->salvar(
            10,
            [['exame_tipo_id' => $tipo->id, 'unidade' => 'anos', 'valor' => 2]],
            AsoPeriodicidadeService::MODO_FUTURO,
            null,
            $user
        );

        $this->assertFalse($resultado['job_despachado']);
        Queue::assertNotPushed(JobRecalcularVencimentoAso::class);

        $this->assertDatabaseHas(EmpresaExameTipoPeriodicidade::TABELA, [
            'empresa_id' => 10,
            'exame_tipo_id' => $tipo->id,
            'unidade' => 'anos',
            'valor' => 2,
        ]);

        $this->assertDatabaseHas(EmpresaExameTipoPeriodicidadeHistorico::TABELA, [
            'empresa_id' => 10,
            'user_id' => $user->id,
            'modo_aplicacao' => 'futuro',
            'job_despachado' => 0,
        ]);

        $this->assertTrue(
            DB::table('activity_log')
                ->where('log_name', 'aso_periodicidade')
                ->where('causer_id', $user->id)
                ->whereNotNull('descricao')
                ->where('descricao', '!=', '')
                ->exists()
        );
    }

    public function test_retroativo_grava_activity_log_com_descricao(): void
    {
        Queue::fake();
        $user = $this->criarUsuario(10);
        $tipo = $this->criarTipo('Periódico');

        $this->service->salvar(
            10,
            [['exame_tipo_id' => $tipo->id, 'unidade' => 'anos', 'valor' => 2]],
            AsoPeriodicidadeService::MODO_RETROATIVO,
            null,
            $user
        );

        $log = DB::table('activity_log')
            ->where('log_name', 'aso_periodicidade')
            ->where('causer_id', $user->id)
            ->orderByDesc('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertNotEmpty($log->descricao);
        $this->assertStringContainsString('retroativo', $log->descricao);
    }

    public function test_retroativo_despacha_job(): void
    {
        Queue::fake();
        $user = $this->criarUsuario(10);
        $tipo = $this->criarTipo('Periódico');

        $resultado = $this->service->salvar(
            10,
            [['exame_tipo_id' => $tipo->id, 'unidade' => 'anos', 'valor' => 2]],
            AsoPeriodicidadeService::MODO_RETROATIVO,
            null,
            $user
        );

        $this->assertTrue($resultado['job_despachado']);
        Queue::assertPushed(JobRecalcularVencimentoAso::class, function (JobRecalcularVencimentoAso $job) use ($tipo) {
            return $job->empresaId === 10
                && $job->dataAplicacao === null
                && count($job->tipos) === 1
                && (int) $job->tipos[0]['exame_tipo_id'] === (int) $tipo->id;
        });
    }

    public function test_a_partir_de_exige_data_e_passa_ao_job(): void
    {
        Queue::fake();
        $user = $this->criarUsuario(10);
        $tipo = $this->criarTipo('Periódico');

        $this->expectException(ValidationException::class);
        $this->service->salvar(
            10,
            [['exame_tipo_id' => $tipo->id, 'unidade' => 'anos', 'valor' => 2]],
            AsoPeriodicidadeService::MODO_A_PARTIR_DE,
            null,
            $user
        );
    }

    public function test_a_partir_de_despacha_com_data(): void
    {
        Queue::fake();
        $user = $this->criarUsuario(10);
        $tipo = $this->criarTipo('Periódico');

        $resultado = $this->service->salvar(
            10,
            [['exame_tipo_id' => $tipo->id, 'unidade' => 'anos', 'valor' => 2]],
            AsoPeriodicidadeService::MODO_A_PARTIR_DE,
            '01/06/2024',
            $user
        );

        $this->assertTrue($resultado['job_despachado']);
        Queue::assertPushed(JobRecalcularVencimentoAso::class, function (JobRecalcularVencimentoAso $job) {
            return $job->dataAplicacao === '2024-06-01';
        });
    }

    public function test_job_lote_recalcula_somente_atual_e_respeita_data(): void
    {
        $tipo = $this->criarTipo('Periódico');

        DB::table('exame_funcionarios')->insert([
            ['id' => 1, 'empresa_id' => 10, 'exame_tipo_id' => $tipo->id, 'feedback_id' => 1, 'formulario_id' => 1, 'empresa_exame_id' => 1, 'user_encaminhou_id' => 1, 'token' => 'a', 'respostas' => '{}'],
            ['id' => 2, 'empresa_id' => 10, 'exame_tipo_id' => $tipo->id, 'feedback_id' => 2, 'formulario_id' => 1, 'empresa_exame_id' => 1, 'user_encaminhou_id' => 1, 'token' => 'b', 'respostas' => '{}'],
            ['id' => 3, 'empresa_id' => 10, 'exame_tipo_id' => $tipo->id, 'feedback_id' => 3, 'formulario_id' => 1, 'empresa_exame_id' => 1, 'user_encaminhou_id' => 1, 'token' => 'c', 'respostas' => '{}'],
        ]);

        DB::table('examesesmts')->insert([
            [
                'id' => 11,
                'empresa_id' => 10,
                'exame_funcionario_id' => 1,
                'feedback_id' => 1,
                'exame_realizado' => 1,
                'data_realizacao' => '2024-01-10',
                'data_vencimento' => '2025-01-10',
                'vencido' => 0,
                'atual' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 12,
                'empresa_id' => 10,
                'exame_funcionario_id' => 2,
                'feedback_id' => 2,
                'exame_realizado' => 1,
                'data_realizacao' => '2023-01-10',
                'data_vencimento' => '2024-01-10',
                'vencido' => 0,
                'atual' => 1,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 13,
                'empresa_id' => 10,
                'exame_funcionario_id' => 3,
                'feedback_id' => 3,
                'exame_realizado' => 1,
                'data_realizacao' => '2024-06-01',
                'data_vencimento' => '2025-06-01',
                'vencido' => 0,
                'atual' => 0,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Cache::put(JobRecalcularVencimentoAso::cacheLockKey(10), 1, 60);
        Cache::put(JobRecalcularVencimentoAso::cacheTiposRestantesKey(10), 1, 60);

        Queue::fake();

        (new JobRecalcularVencimentoAsoLote(
            10,
            (int) $tipo->id,
            'anos',
            2,
            0,
            '2024-01-01',
            300
        ))->handle();

        $atualizado = DB::table('examesesmts')->where('id', 11)->first();
        $antigo = DB::table('examesesmts')->where('id', 12)->first();
        $naoAtual = DB::table('examesesmts')->where('id', 13)->first();

        $this->assertSame('2026-01-09', $atualizado->data_vencimento);
        $this->assertSame('2024-01-10', $antigo->data_vencimento);
        $this->assertSame('2025-06-01', $naoAtual->data_vencimento);
    }

    public function test_lock_impede_orquestrador_duplicado(): void
    {
        Queue::fake();
        Cache::put(JobRecalcularVencimentoAso::cacheLockKey(10), 1, 60);

        (new JobRecalcularVencimentoAso(10, [
            ['exame_tipo_id' => 1, 'unidade' => 'anos', 'valor' => 2],
        ]))->handle();

        Queue::assertNotPushed(JobRecalcularVencimentoAsoLote::class);
    }

    private function criarTabelas(): void
    {
        Schema::dropIfExists('empresas_exame_tipos_periodicidades_historicos');
        Schema::dropIfExists('empresas_exame_tipos_periodicidades');
        Schema::dropIfExists('examesesmts');
        Schema::dropIfExists('exame_funcionarios');
        Schema::dropIfExists('exame_tipos');
        Schema::dropIfExists('users');
        Schema::dropIfExists('activity_log');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome')->nullable();
            $table->string('email')->nullable();
            $table->string('tipo')->nullable();
            $table->boolean('ativo')->default(true);
            $table->boolean('require_password_reset')->default(false);
            $table->unsignedInteger('password_reset_days')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('exame_tipos', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('empresas_exame_tipos_periodicidades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('exame_tipo_id');
            $table->string('unidade', 10);
            $table->unsignedInteger('valor');
            $table->timestamps();
            $table->unique(['empresa_id', 'exame_tipo_id']);
        });

        Schema::create('empresas_exame_tipos_periodicidades_historicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('user_id');
            $table->string('modo_aplicacao', 20);
            $table->date('data_aplicacao')->nullable();
            $table->boolean('job_despachado')->default(false);
            $table->json('alteracoes');
            $table->string('descricao', 500);
            $table->timestamps();
        });

        Schema::create('exame_funcionarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('exame_tipo_id')->nullable();
            $table->unsignedBigInteger('feedback_id')->nullable();
            $table->unsignedBigInteger('formulario_id')->nullable();
            $table->unsignedBigInteger('empresa_exame_id')->nullable();
            $table->unsignedBigInteger('user_encaminhou_id')->nullable();
            $table->string('token')->nullable();
            $table->text('respostas')->nullable();
            $table->timestamps();
        });

        Schema::create('examesesmts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('exame_funcionario_id');
            $table->unsignedBigInteger('feedback_id')->nullable();
            $table->boolean('exame_realizado')->default(false);
            $table->date('data_realizacao')->nullable();
            $table->date('data_vencimento')->nullable();
            $table->boolean('vencido')->default(false);
            $table->boolean('atual')->default(false);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->text('descricao'); // NOT NULL — espelha produção MyBP
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->string('event')->nullable();
            $table->timestamps();
        });
    }

    private function criarUsuario(int $empresaId): User
    {
        $user = new User();
        $user->forceFill([
            'empresa_id' => $empresaId,
            'nome' => 'Tester',
            'email' => 'tester@example.com',
        ]);
        $user->save();

        return $user;
    }

    private function criarTipo(string $label): ExameTipo
    {
        $tipo = new ExameTipo();
        $tipo->forceFill([
            'label' => $label,
            'ativo' => true,
        ]);
        $tipo->save();

        return $tipo;
    }
}
