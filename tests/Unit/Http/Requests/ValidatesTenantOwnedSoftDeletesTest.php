<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Http\Requests\Movimentacao\StoreValorExtraPrevistaRequest;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidatesTenantOwnedSoftDeletesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \App\Http\Requests\Concerns\ValidatesTenantOwned::limparCacheSoftDeletes();
        $this->actingAs(new User(['empresa_id' => 10, 'id' => 1]));

        Schema::dropIfExists('users');
        Schema::dropIfExists('centro_custos');
        Schema::dropIfExists('plano_contas');

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
            $table->string('label')->nullable();
            $table->softDeletes();
        });
    }

    public function test_rejeita_colaborador_soft_deleted(): void
    {
        DB::table('centro_custos')->insert([
            'id' => 1,
            'empresa_id' => 10,
            'label' => 'CC',
            'deleted_at' => null,
        ]);
        DB::table('users')->insert([
            'id' => 10,
            'empresa_id' => 10,
            'nome' => 'Apagado',
            'ativo' => 1,
            'deleted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new StoreValorExtraPrevistaRequest();
        $validator = Validator::make([
            'centro_custo_id' => 1,
            'colaborador_id' => 10,
            'tipo' => 'hora_extra',
            'periodo_dias' => 30,
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('colaborador_id', $validator->errors()->toArray());
    }

    public function test_rejeita_centro_custo_soft_deleted(): void
    {
        DB::table('centro_custos')->insert([
            'id' => 1,
            'empresa_id' => 10,
            'label' => 'CC apagado',
            'deleted_at' => now(),
        ]);
        DB::table('users')->insert([
            'id' => 10,
            'empresa_id' => 10,
            'nome' => 'Colab',
            'ativo' => 1,
            'deleted_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new StoreValorExtraPrevistaRequest();
        $validator = Validator::make([
            'centro_custo_id' => 1,
            'colaborador_id' => 10,
            'tipo' => 'hora_extra',
            'periodo_dias' => 30,
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('centro_custo_id', $validator->errors()->toArray());
    }

    public function test_aceita_ids_ativos_da_empresa(): void
    {
        DB::table('centro_custos')->insert([
            'id' => 1,
            'empresa_id' => 10,
            'label' => 'CC',
            'deleted_at' => null,
        ]);
        DB::table('users')->insert([
            'id' => 10,
            'empresa_id' => 10,
            'nome' => 'Colab',
            'ativo' => 1,
            'deleted_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new StoreValorExtraPrevistaRequest();
        $validator = Validator::make([
            'centro_custo_id' => 1,
            'colaborador_id' => 10,
            'tipo' => 'hora_extra',
            'periodo_dias' => 30,
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_tabela_sem_deleted_at_nao_quebra_exists(): void
    {
        Schema::dropIfExists('plano_contas');
        Schema::create('plano_contas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('descricao')->nullable();
        });
        DB::table('plano_contas')->insert(['id' => 5, 'empresa_id' => 10, 'descricao' => 'Plano']);

        $probe = new class extends FormRequest {
            use ValidatesTenantOwned;

            public function rules(): array
            {
                return [
                    'plano_id' => ['required', 'integer', $this->existsNaEmpresa('plano_contas')],
                ];
            }
        };

        $validator = Validator::make(['plano_id' => 5], $probe->rules());
        $this->assertTrue($validator->passes());
    }
}
