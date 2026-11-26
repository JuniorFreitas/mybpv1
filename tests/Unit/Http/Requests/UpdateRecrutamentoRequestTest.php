<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Recrutamento\UpdateRecrutamentoRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateRecrutamentoRequestTest extends TestCase
{
    public function test_regras_aceitam_payload_minimo_valido(): void
    {
        $request = new UpdateRecrutamentoRequest();
        $validator = Validator::make([
            'curriculos' => [
                'id' => 10,
                'nome' => 'Candidato Teste',
                'email' => 'candidato@exemplo.test',
            ],
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_regras_rejeitam_sem_curriculo(): void
    {
        $request = new UpdateRecrutamentoRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('curriculos', $validator->errors()->toArray());
    }

    public function test_regras_rejeitam_sem_nome(): void
    {
        $request = new UpdateRecrutamentoRequest();
        $validator = Validator::make([
            'curriculos' => ['id' => 1],
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('curriculos.nome', $validator->errors()->toArray());
    }
}
