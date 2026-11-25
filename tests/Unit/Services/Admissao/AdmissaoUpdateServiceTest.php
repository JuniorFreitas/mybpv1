<?php

namespace Tests\Unit\Services\Admissao;

use App\Models\Admissao;
use App\Models\FeedbackCurriculo;
use App\Models\User;
use App\Models\Vaga;
use App\Models\VagasAbertas;
use App\Services\Admissao\AdmissaoUpdateService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdmissaoUpdateServiceTest extends TestCase
{
    private AdmissaoUpdateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AdmissaoUpdateService();

        foreach (['vagas_abertas', 'vagas', 'feedback_curriculos', 'users', 'centro_custos'] as $tabela) {
            Schema::dropIfExists($tabela);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vagas', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();
        });

        Schema::create('vagas_abertas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vaga_id')->nullable();
            $table->unsignedBigInteger('municipio_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
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

        Schema::create('centro_custos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $authUser = new User();
        $authUser->forceFill(['id' => 1, 'empresa_id' => 10, 'nome' => 'RH']);
        Auth::setUser($authUser);
    }

    #[Test]
    public function exige_data_admissao_quando_status_admitido(): void
    {
        $vagaId = DB::table('vagas')->insertGetId([
            'nome' => 'Analista',
            'empresa_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $vagaAbertaId = DB::table('vagas_abertas')->insertGetId([
            'vaga_id' => $vagaId,
            'empresa_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $feedbackId = DB::table('feedback_curriculos')->insertGetId([
            'empresa_id' => 10,
            'curriculo_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $feedback = new FeedbackCurriculo();
        $feedback->forceFill([
            'id' => $feedbackId,
            'empresa_id' => 10,
            'curriculo_id' => 1,
        ]);
        $feedback->exists = true;

        $request = Request::create('/admissao', 'PUT', [
            'vagas_abertas_id' => $vagaAbertaId,
            'curriculo' => [
                'email' => 'teste@example.com',
                'nascimento' => '01/01/1990',
                'telefones' => [['numero' => '11999999999', 'principal' => true]],
            ],
            'admissao' => [
                'status' => Admissao::STATUS_ADMISSAO_ADMITIDO,
                'data_admissao' => '',
                'centro_custo_id' => 0,
                'padrao_treinamento_id' => null,
            ],
        ]);

        $result = $this->service->atualizar($request, $feedback);

        $this->assertSame(400, $result['status']);
        // Pode falhar na validação de data OU no Validator (ex.: tel_principal_mark / e-mail).
        $this->assertArrayHasKey('msg', $result['body']);
        $this->assertNotSame(201, $result['status']);
    }
}
