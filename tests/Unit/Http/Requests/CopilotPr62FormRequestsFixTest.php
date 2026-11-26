<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Ponto\UpdateAjusteJornadaRequest;
use App\Http\Requests\Treinamentos\StoreEmpresaExameRequest;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CopilotPr62FormRequestsFixTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \App\Http\Requests\Concerns\ValidatesTenantOwned::limparCacheSoftDeletes();
        $this->actingAs(new User(['empresa_id' => 10, 'id' => 1]));
    }

    public function test_empresa_exame_prepare_sem_ativo_nao_explode(): void
    {
        $request = StoreEmpresaExameRequest::create('/fake', 'POST', [
            'nome' => 'Clinica',
            'dados' => ['cnpj' => '12.345.678/0001-90', 'email' => 'a@b.com'],
        ]);
        $request->setContainer($this->app)->setRedirector($this->app['redirect']);
        $request->setUserResolver(fn () => auth()->user());

        $ref = new \ReflectionMethod($request, 'prepareForValidation');
        $ref->setAccessible(true);
        $ref->invoke($request);

        $this->assertFalse($request->exists('ativo'));

        $validator = Validator::make($request->all(), $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('ativo', $validator->errors()->toArray());
    }

    public function test_ajuste_jornada_aceita_ocorrencia_global(): void
    {
        Schema::dropIfExists('ocorrencias_jornada');
        Schema::create('ocorrencias_jornada', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome')->nullable();
            $table->softDeletes();
        });

        DB::table('ocorrencias_jornada')->insert([
            ['id' => 1, 'empresa_id' => null, 'nome' => 'DIA_TRABALHADO', 'deleted_at' => null],
            ['id' => 2, 'empresa_id' => 10, 'nome' => 'Empresa', 'deleted_at' => null],
            ['id' => 3, 'empresa_id' => 99, 'nome' => 'Outra', 'deleted_at' => null],
        ]);

        $request = new UpdateAjusteJornadaRequest();
        $request->setUserResolver(fn () => auth()->user());

        $okGlobal = Validator::make([
            'periodos' => [['entrada' => '08:00', 'saida' => '12:00']],
            'ocorrencia_id' => 1,
        ], $request->rules());
        $this->assertFalse($okGlobal->fails(), $okGlobal->errors()->toJson());

        $okEmpresa = Validator::make([
            'periodos' => [['entrada' => '08:00', 'saida' => '12:00']],
            'ocorrencia_id' => 2,
        ], $request->rules());
        $this->assertFalse($okEmpresa->fails(), $okEmpresa->errors()->toJson());

        $outra = Validator::make([
            'periodos' => [['entrada' => '08:00', 'saida' => '12:00']],
            'ocorrencia_id' => 3,
        ], $request->rules());
        $this->assertTrue($outra->fails());
        $this->assertArrayHasKey('ocorrencia_id', $outra->errors()->toArray());
    }
}
