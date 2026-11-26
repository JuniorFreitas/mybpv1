<?php

namespace Tests\Unit\Services\Admissao;

use App\Models\Admissao;
use App\Models\Demissao;
use App\Models\User;
use App\Services\Admissao\AdmissaoDemissaoPrivilegioService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdmissaoDemissaoPrivilegioServiceTest extends TestCase
{
    private AdmissaoDemissaoPrivilegioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AdmissaoDemissaoPrivilegioService();

        foreach (['activity_log', 'demissaos', 'admissoes', 'feedback_curriculos', 'users'] as $tabela) {
            Schema::dropIfExists($tabela);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('feedback_curriculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('usuario_entrevista_marcado')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('admissoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('demissaos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable();
            $table->boolean('cipa')->default(false);
            $table->date('data_desmobilizacao')->nullable();
            $table->unsignedBigInteger('motivo_rescisao_id')->nullable();
            $table->string('outro_motivo')->nullable();
            $table->unsignedBigInteger('tipo_aviso_id')->nullable();
            $table->string('solicitado_por')->nullable();
            $table->string('comentario')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
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

        // ScopeEmpresa / ScopeOrderFeedback em FeedbackCurriculo exige auth()->user().
        $authUser = new User();
        $authUser->forceFill(['id' => 999, 'empresa_id' => 10, 'ativo' => true]);
        Auth::setUser($authUser);
    }

    #[Test]
    public function demite_colaborador_da_mesma_empresa(): void
    {
        $userId = DB::table('users')->insertGetId([
            'empresa_id' => 10,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $feedbackId = DB::table('feedback_curriculos')->insertGetId([
            'empresa_id' => 10,
            'curriculo_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admissaoId = DB::table('admissoes')->insertGetId([
            'feedback_id' => $feedbackId,
            'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->service->demitir([
            'admissao_id' => $admissaoId,
            'feedback_id' => $feedbackId,
            'curriculo_id' => $userId,
            'cipa' => false,
            'data_desmobilizacao' => '01/01/2026',
            'motivo_rescisao_id' => 1,
            'tipo_aviso_id' => 1,
            'solicitado_por' => 'RH Teste',
            'comentario' => 'Teste unitário',
            'user_id' => 999,
        ], 10, 999);

        $this->assertSame(200, $result['status']);
        $this->assertSame(Admissao::STATUS_DEMITIDO, DB::table('admissoes')->where('id', $admissaoId)->value('status'));
        $this->assertSame(0, (int) DB::table('users')->where('id', $userId)->value('ativo'));
        $demissao = Demissao::query()->where('feedback_id', $feedbackId)->first();
        $this->assertNotNull($demissao);
        $this->assertSame(999, (int) $demissao->user_id);
    }

    #[Test]
    public function recusa_admissao_de_outra_empresa(): void
    {
        $feedbackId = DB::table('feedback_curriculos')->insertGetId([
            'empresa_id' => 99,
            'curriculo_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admissaoId = DB::table('admissoes')->insertGetId([
            'feedback_id' => $feedbackId,
            'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->service->demitir([
            'admissao_id' => $admissaoId,
            'feedback_id' => $feedbackId,
            'curriculo_id' => 1,
            'cipa' => false,
            'data_desmobilizacao' => '01/01/2026',
            'motivo_rescisao_id' => 1,
            'tipo_aviso_id' => 1,
            'solicitado_por' => 'RH Teste',
            'comentario' => null,
            'user_id' => 1,
        ], 10, 999);

        $this->assertSame(400, $result['status']);
        $this->assertSame(Admissao::STATUS_ADMISSAO_ADMITIDO, DB::table('admissoes')->where('id', $admissaoId)->value('status'));
        $this->assertSame(0, Demissao::query()->count());
    }

    #[Test]
    public function recusa_curriculo_id_manipulado_de_outro_usuario(): void
    {
        $vitimaId = DB::table('users')->insertGetId([
            'empresa_id' => 20,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $colaboradorId = DB::table('users')->insertGetId([
            'empresa_id' => 10,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $feedbackId = DB::table('feedback_curriculos')->insertGetId([
            'empresa_id' => 10,
            'curriculo_id' => $colaboradorId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admissaoId = DB::table('admissoes')->insertGetId([
            'feedback_id' => $feedbackId,
            'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $this->service->demitir([
            'admissao_id' => $admissaoId,
            'feedback_id' => $feedbackId,
            'curriculo_id' => $vitimaId, // IDOR: tenta desativar usuário de outra empresa
            'cipa' => false,
            'data_desmobilizacao' => '01/01/2026',
            'motivo_rescisao_id' => 1,
            'tipo_aviso_id' => 1,
            'solicitado_por' => 'RH Teste',
            'comentario' => null,
            'user_id' => 999,
        ], 10, 999);

        $this->assertSame(400, $result['status']);
        $this->assertSame(1, (int) DB::table('users')->where('id', $vitimaId)->value('ativo'));
        $this->assertSame(1, (int) DB::table('users')->where('id', $colaboradorId)->value('ativo'));
        $this->assertSame(0, Demissao::query()->count());
    }
}
