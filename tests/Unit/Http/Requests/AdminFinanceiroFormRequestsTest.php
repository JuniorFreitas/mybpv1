<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Admin\StorePapelRequest;
use App\Http\Requests\Financeiro\StoreLancamentoRequest;
use App\Http\Requests\Financeiro\StorePlanoContaRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AdminFinanceiroFormRequestsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(new User(['empresa_id' => 10, 'id' => 1]));
    }

    public function test_papel_forca_empresa_autenticada(): void
    {
        $request = StorePapelRequest::create('/fake', 'POST', [
            'nome' => 'Papel Teste',
            'descricao' => 'Descrição válida',
            'ativo' => true,
            'empresa_id' => 999,
        ]);
        $request->setContainer($this->app)->setRedirector($this->app['redirect']);
        $request->setUserResolver(fn () => auth()->user());

        $ref = new \ReflectionMethod($request, 'prepareForValidation');
        $ref->setAccessible(true);
        $ref->invoke($request);

        $this->assertSame(10, (int) $request->input('empresa_id'));
    }

    public function test_plano_conta_rejeita_sem_descricao(): void
    {
        $request = new StorePlanoContaRequest();
        $validator = Validator::make([
            'operacao' => 'C',
            'categoria_plano_id' => 1,
            'ativo' => true,
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('descricao', $validator->errors()->toArray());
    }

    public function test_lancamento_rejeita_sem_campos(): void
    {
        $request = new StoreLancamentoRequest();
        $validator = Validator::make([], $request->rules());
        $this->assertTrue($validator->fails());
        $erros = $validator->errors()->toArray();
        $this->assertArrayHasKey('data_hora', $erros);
        $this->assertArrayHasKey('plano_id', $erros);
        $this->assertArrayHasKey('operacao', $erros);
    }
}
