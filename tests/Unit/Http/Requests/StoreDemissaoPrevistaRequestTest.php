<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Movimentacao\StoreDemissaoPrevistaRequest;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreDemissaoPrevistaRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \App\Http\Requests\Concerns\ValidatesTenantOwned::limparCacheSoftDeletes();
        $this->actingAs(new User(['empresa_id' => 10, 'id' => 1]));

        Schema::dropIfExists('users');
        Schema::dropIfExists('centro_custos');
        Schema::dropIfExists('centro_custo_filials');

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
        Schema::create('centro_custo_filials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->softDeletes();
        });
    }

    public function test_regras_aceitam_payload_minimo(): void
    {
        DB::table('users')->insert(['id' => 10, 'empresa_id' => 10, 'nome' => 'Colab', 'ativo' => 1]);

        $request = new StoreDemissaoPrevistaRequest();
        $validator = Validator::make([
            'colaborador_id' => 10,
            'valor_format' => '1.000,00',
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_regras_rejeitam_sem_colaborador(): void
    {
        $request = new StoreDemissaoPrevistaRequest();
        $validator = Validator::make([
            'valor_format' => '100,00',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('colaborador_id', $validator->errors()->toArray());
    }

    public function test_rejeita_colaborador_de_outra_empresa(): void
    {
        DB::table('users')->insert(['id' => 10, 'empresa_id' => 99, 'nome' => 'Alien', 'ativo' => 1]);

        $request = new StoreDemissaoPrevistaRequest();
        $validator = Validator::make([
            'colaborador_id' => 10,
            'valor_format' => '1.000,00',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('colaborador_id', $validator->errors()->toArray());
    }
}
