<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Movimentacao\UpdateFeriasPrevistaRequest;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateFeriasPrevistaRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(new User(['empresa_id' => 10, 'id' => 1]));

        Schema::dropIfExists('users');
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
        Schema::create('centro_custos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
        });
        Schema::create('periodos_aquisitivos', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
        });
    }

    public function test_regras_aceitam_payload_valido(): void
    {
        DB::table('periodos_aquisitivos')->insert(['id' => 1, 'label' => '2025/2026']);

        $request = new UpdateFeriasPrevistaRequest();
        $validator = Validator::make([
            'periodo_aquisitivo_id' => 1,
            'data_saida' => '01/12/2026',
            'data_retorno' => '16/12/2026',
            'ultima_data' => '01/12/2026',
            'qnt_dias' => 15,
            'dias_saldo' => 30,
            'tem_faltas' => false,
            'qnt_faltas' => 0,
            'abono_pecuniario' => false,
            'adiantamento_decimo_terceiro' => false,
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_regras_rejeitam_sem_datas(): void
    {
        DB::table('periodos_aquisitivos')->insert(['id' => 1, 'label' => '2025/2026']);

        $request = new UpdateFeriasPrevistaRequest();
        $validator = Validator::make([
            'periodo_aquisitivo_id' => 1,
            'qnt_dias' => 15,
            'dias_saldo' => 30,
            'tem_faltas' => false,
            'qnt_faltas' => 0,
            'abono_pecuniario' => false,
            'adiantamento_decimo_terceiro' => false,
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $erros = $validator->errors()->toArray();
        $this->assertArrayHasKey('data_saida', $erros);
        $this->assertArrayHasKey('data_retorno', $erros);
    }
}
