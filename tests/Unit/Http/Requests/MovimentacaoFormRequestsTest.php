<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Movimentacao\StoreAdmissoesPrevistaRequest;
use App\Http\Requests\Movimentacao\StoreIntermitenteFixoPrevistaRequest;
use App\Http\Requests\Movimentacao\StoreMudaCargoPrevistaRequest;
use App\Http\Requests\Movimentacao\StoreMudancaCargoRequest;
use App\Http\Requests\Movimentacao\StoreRequisicaoVagaRequest;
use App\Http\Requests\Movimentacao\StoreTransferenciaPrevistaRequest;
use App\Http\Requests\Movimentacao\StoreValorExtraPrevistaRequest;
use App\Http\Requests\Movimentacao\UpdateAdmissoesPrevistaRequest;
use App\Http\Requests\Movimentacao\UpdateIntermitenteFixoPrevistaRequest;
use App\Http\Requests\Movimentacao\UpdateMudaCargoPrevistaRequest;
use App\Http\Requests\Movimentacao\UpdateRequisicaoVagaRequest;
use App\Http\Requests\Movimentacao\UpdateTransferenciaPrevistaRequest;
use App\Http\Requests\Movimentacao\UpdateValorExtraPrevistaRequest;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Exists;
use Tests\TestCase;

class MovimentacaoFormRequestsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \App\Http\Requests\Concerns\ValidatesTenantOwned::limparCacheSoftDeletes();
        $this->actingAs(new User(['empresa_id' => 10, 'id' => 1]));
        $this->garantirTabelasTenant();
    }

    public function test_valor_extra_store_e_update_rejeitam_sem_campos_obrigatorios(): void
    {
        foreach ([new StoreValorExtraPrevistaRequest(), new UpdateValorExtraPrevistaRequest()] as $request) {
            $validator = Validator::make([], $request->rules());
            $this->assertTrue($validator->fails());
            $erros = $validator->errors()->toArray();
            $this->assertArrayHasKey('centro_custo_id', $erros);
            $this->assertArrayHasKey('colaborador_id', $erros);
            $this->assertArrayHasKey('tipo', $erros);
            $this->assertArrayHasKey('periodo_dias', $erros);
        }
    }

    public function test_muda_cargo_prevista_rejeita_sem_campos_obrigatorios(): void
    {
        foreach ([new StoreMudaCargoPrevistaRequest(), new UpdateMudaCargoPrevistaRequest()] as $request) {
            $validator = Validator::make([], $request->rules());
            $this->assertTrue($validator->fails());
            $erros = $validator->errors()->toArray();
            $this->assertArrayHasKey('colaborador_id', $erros);
            $this->assertArrayHasKey('novo_cargo_id', $erros);
            $this->assertArrayHasKey('novo_salario_format', $erros);
        }
    }

    public function test_transferencia_store_rejeita_sem_campos_obrigatorios(): void
    {
        $request = new StoreTransferenciaPrevistaRequest();
        $validator = Validator::make([], $request->rules());
        $this->assertTrue($validator->fails());
        $erros = $validator->errors()->toArray();
        $this->assertArrayHasKey('centro_custo_origem_id', $erros);
        $this->assertArrayHasKey('centro_custo_destino_id', $erros);
        $this->assertArrayHasKey('colaborador_id', $erros);
    }

    public function test_transferencia_update_rejeita_sem_colaborador(): void
    {
        $request = new UpdateTransferenciaPrevistaRequest();
        $validator = Validator::make([], $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('colaborador_id', $validator->errors()->toArray());
    }

    public function test_intermitente_fixo_store_exige_vagas_abertas(): void
    {
        $request = new StoreIntermitenteFixoPrevistaRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $erros = $validator->errors()->toArray();
        $this->assertArrayHasKey('anterior_vaga_aberta_id', $erros);
        $this->assertArrayHasKey('nova_vaga_aberta_id', $erros);
        $this->assertArrayHasKey('novo_cargo_id', $erros);
    }

    public function test_requisicao_vaga_store_e_update_rejeitam_sem_campos_obrigatorios(): void
    {
        foreach ([new StoreRequisicaoVagaRequest(), new UpdateRequisicaoVagaRequest()] as $request) {
            $validator = Validator::make([], $request->rules());
            $this->assertTrue($validator->fails());
            $erros = $validator->errors()->toArray();
            $this->assertArrayHasKey('centro_custo_id', $erros);
            $this->assertArrayHasKey('cargo_id', $erros);
            $this->assertArrayHasKey('outras_informacoes.gestor_id', $erros);
        }
    }

    public function test_admissoes_prevista_rejeitam_sem_campos_obrigatorios(): void
    {
        foreach ([new StoreAdmissoesPrevistaRequest(), new UpdateAdmissoesPrevistaRequest()] as $request) {
            $validator = Validator::make([], $request->rules());
            $this->assertTrue($validator->fails());
            $erros = $validator->errors()->toArray();
            $this->assertArrayHasKey('centro_custo_id', $erros);
            $this->assertArrayHasKey('cargo_id', $erros);
            $this->assertArrayHasKey('salario_format', $erros);
        }
    }

    public function test_mudanca_cargo_store_rejeita_sem_colaborador_e_gestor(): void
    {
        $request = new StoreMudancaCargoRequest();
        $validator = Validator::make([
            'mantem_centro_custo' => true,
            'mantem_cargo' => true,
            'mantem_funcao' => true,
            'mantem_salario' => true,
            'treinamento_funcao' => false,
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $erros = $validator->errors()->toArray();
        $this->assertArrayHasKey('colaborador_id', $erros);
        $this->assertArrayHasKey('gestor_id', $erros);
        $this->assertArrayHasKey('admissao_id', $erros);
    }

    public function test_regras_de_ids_usam_exists_com_empresa(): void
    {
        $request = new StoreTransferenciaPrevistaRequest();
        $rules = $request->rules();

        foreach (['centro_custo_origem_id', 'centro_custo_destino_id', 'colaborador_id'] as $campo) {
            $this->assertTrue(
                collect($rules[$campo])->contains(fn ($r) => $r instanceof Exists),
                "Campo {$campo} deve ter Rule::exists tenant"
            );
        }
    }

    public function test_rejeita_centro_custo_de_outra_empresa(): void
    {
        DB::table('centro_custos')->insert([
            ['id' => 1, 'empresa_id' => 10, 'label' => 'CC própria'],
            ['id' => 2, 'empresa_id' => 99, 'label' => 'CC alienígena'],
        ]);
        DB::table('users')->insert([
            'id' => 10,
            'empresa_id' => 10,
            'nome' => 'Colab',
            'ativo' => 1,
        ]);

        $request = new StoreValorExtraPrevistaRequest();
        $validator = Validator::make([
            'centro_custo_id' => 2,
            'colaborador_id' => 10,
            'tipo' => 'hora_extra',
            'periodo_dias' => 30,
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('centro_custo_id', $validator->errors()->toArray());
    }

    public function test_aceita_centro_custo_e_colaborador_da_mesma_empresa(): void
    {
        DB::table('centro_custos')->insert([
            'id' => 1,
            'empresa_id' => 10,
            'label' => 'CC própria',
        ]);
        DB::table('users')->insert([
            'id' => 10,
            'empresa_id' => 10,
            'nome' => 'Colab',
            'ativo' => 1,
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

    private function garantirTabelasTenant(): void
    {
        Schema::dropIfExists('centro_custos');
        Schema::dropIfExists('users');

        Schema::create('centro_custos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('label')->nullable();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome')->nullable();
            $table->boolean('ativo')->default(true);
        });
    }
}
