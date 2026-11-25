<?php

namespace Tests\Unit\Services\Admissao;

use App\Models\Admissao;
use App\Models\User;
use App\Services\Admissao\AdmissaoCadastraMassaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdmissaoCadastraMassaServiceTest extends TestCase
{
    private AdmissaoCadastraMassaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AdmissaoCadastraMassaService();

        foreach (['activity_log', 'avaliacao_noventa_vencimentos', 'admissoes', 'feedback_curriculos', 'users'] as $tabela) {
            Schema::dropIfExists($tabela);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome')->nullable();
            $table->string('tipo')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('feedback_curriculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('selecionado')->nullable();
            $table->timestamp('data_entrevista')->nullable();
            $table->unsignedBigInteger('usuario_entrevista_marcado')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('admissoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable();
            $table->string('tipo_admissao')->nullable();
            $table->string('prazo_experiencia')->nullable();
            $table->string('data_encerramento')->nullable();
            $table->string('documento_portaria')->nullable();
            $table->string('status_carteira_treinamento')->nullable();
            $table->unsignedBigInteger('padrao_treinamento_id')->nullable();
            $table->string('status')->nullable();
            $table->string('data_admissao')->nullable();
            $table->string('data_entrega_area')->nullable();
            $table->string('biometria')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('avaliacao_noventa_vencimentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable();
            $table->string('prazo_dez_inicial')->nullable();
            $table->string('prazo_cinco_inicial')->nullable();
            $table->string('prazo_dia_inicial')->nullable();
            $table->string('prazo_dez_final')->nullable();
            $table->string('prazo_cinco_final')->nullable();
            $table->string('prazo_dia_final')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->text('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->string('event')->nullable();
            $table->string('descricao')->nullable();
            $table->timestamps();
        });

        $authUser = new User();
        $authUser->forceFill(['id' => 1, 'empresa_id' => 10, 'nome' => 'RH']);
        Auth::setUser($authUser);
    }

    #[Test]
    public function recusa_selecionados_vazio(): void
    {
        $result = $this->service->executar(['selecionados' => []], 10);

        $this->assertSame(400, $result['status']);
        $this->assertArrayHasKey('erros', $result['body']);
    }

    #[Test]
    public function recusa_feedback_de_outra_empresa(): void
    {
        $feedbackId = DB::table('feedback_curriculos')->insertGetId([
            'empresa_id' => 99,
            'curriculo_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->service->executar([
            'selecionados' => [$feedbackId],
            'tipo_admissao' => 'INTERMITENTE',
            'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
            'data_admissao' => '01/01/2026',
        ], 10);

        $this->assertSame(400, $result['status']);
        $this->assertSame(0, DB::table('admissoes')->count());
    }

    #[Test]
    public function cria_admissao_sem_duplicar_quando_ja_existe(): void
    {
        $feedbackId = DB::table('feedback_curriculos')->insertGetId([
            'empresa_id' => 10,
            'curriculo_id' => 1,
            'selecionado' => 'nao',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('admissoes')->insert([
            'feedback_id' => $feedbackId,
            'status' => 'EM PROCESSO',
            'tipo_admissao' => 'FIXO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->service->executar([
            'selecionados' => [$feedbackId],
            'tipo_admissao' => 'INTERMITENTE',
            'prazo_experiencia' => null,
            'data_encerramento' => null,
            'documento_portaria' => null,
            'status_carteira_treinamento' => null,
            'padrao_treinamento_id' => null,
            'status' => 'EM PROCESSO',
            'data_admissao' => '01/01/2026',
            'data_entrega_area' => null,
            'biometria' => null,
        ], 10);

        $this->assertSame(201, $result['status']);
        $this->assertSame(1, DB::table('admissoes')->where('feedback_id', $feedbackId)->count());
        $this->assertSame('INTERMITENTE', DB::table('admissoes')->where('feedback_id', $feedbackId)->value('tipo_admissao'));
        $this->assertSame('sim', DB::table('feedback_curriculos')->where('id', $feedbackId)->value('selecionado'));
    }
}
