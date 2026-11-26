<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Movimentacao\StoreFeriasPrevistaRequest;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreFeriasPrevistaRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        StoreFeriasPrevistaRequest::limparCacheSoftDeletes();
        $this->actingAs(new User(['empresa_id' => 10, 'id' => 1]));

        Schema::dropIfExists('users');
        Schema::dropIfExists('feedback_curriculos');
        Schema::dropIfExists('centro_custos');
        Schema::dropIfExists('periodos_aquisitivos');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome')->nullable();
            $table->boolean('ativo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('feedback_curriculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('centro_custos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
        });
        Schema::create('periodos_aquisitivos', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->integer('ano_inicial')->nullable();
            $table->integer('ano_final')->nullable();
        });
    }

    public function test_regras_aceitam_payload_valido_com_feedback_da_empresa(): void
    {
        DB::table('users')->insert([
            ['id' => 20, 'empresa_id' => 10, 'nome' => 'Gestor', 'ativo' => 1],
        ]);
        DB::table('feedback_curriculos')->insert([
            ['id' => 100, 'empresa_id' => 10, 'curriculo_id' => 1],
        ]);
        DB::table('periodos_aquisitivos')->insert(['id' => 1, 'label' => '2025/2026']);

        $request = new StoreFeriasPrevistaRequest();
        $validator = Validator::make([
            'qnt_dias' => 15,
            'dias_saldo' => 30,
            'periodo_aquisitivo_id' => 1,
            'colaborador_id' => 100, // feedback_curriculos.id
            'gestor_id' => 20,
            'data_admissao' => '01/01/2020',
            'ultima_data' => '01/12/2026',
        ], $request->rules());

        $this->assertTrue($validator->passes(), (string) $validator->errors());
    }

    public function test_regras_rejeitam_colaborador_que_e_user_id_e_nao_feedback(): void
    {
        DB::table('users')->insert([
            ['id' => 10, 'empresa_id' => 10, 'nome' => 'ColabUser', 'ativo' => 1],
            ['id' => 20, 'empresa_id' => 10, 'nome' => 'Gestor', 'ativo' => 1],
        ]);
        DB::table('periodos_aquisitivos')->insert(['id' => 1, 'label' => '2025/2026']);

        $request = new StoreFeriasPrevistaRequest();
        $validator = Validator::make([
            'qnt_dias' => 15,
            'dias_saldo' => 30,
            'periodo_aquisitivo_id' => 1,
            'colaborador_id' => 10, // users.id — inválido no fluxo de férias
            'gestor_id' => 20,
            'data_admissao' => '01/01/2020',
            'ultima_data' => '01/12/2026',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('colaborador_id', $validator->errors()->toArray());
    }

    public function test_regras_rejeitam_feedback_de_outra_empresa(): void
    {
        DB::table('users')->insert([
            ['id' => 20, 'empresa_id' => 10, 'nome' => 'Gestor', 'ativo' => 1],
        ]);
        DB::table('feedback_curriculos')->insert([
            ['id' => 100, 'empresa_id' => 99, 'curriculo_id' => 1],
        ]);
        DB::table('periodos_aquisitivos')->insert(['id' => 1, 'label' => '2025/2026']);

        $request = new StoreFeriasPrevistaRequest();
        $validator = Validator::make([
            'qnt_dias' => 15,
            'dias_saldo' => 30,
            'periodo_aquisitivo_id' => 1,
            'colaborador_id' => 100,
            'gestor_id' => 20,
            'data_admissao' => '01/01/2020',
            'ultima_data' => '01/12/2026',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('colaborador_id', $validator->errors()->toArray());
    }

    public function test_regras_rejeitam_sem_colaborador_e_gestor(): void
    {
        $request = new StoreFeriasPrevistaRequest();
        $validator = Validator::make([
            'qnt_dias' => 15,
            'dias_saldo' => 30,
            'periodo_aquisitivo_id' => 1,
            'ultima_data' => '01/12/2026',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $erros = $validator->errors()->toArray();
        $this->assertArrayHasKey('colaborador_id', $erros);
        $this->assertArrayHasKey('gestor_id', $erros);
        $this->assertArrayHasKey('data_admissao', $erros);
    }
}
