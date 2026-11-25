<?php

namespace App\Http\Requests\Movimentacao;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRequisicaoVagaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'centro_custo_id' => $this->regraIdNaEmpresa('centro_custos'),
            'cargo_id' => $this->regraIdNaEmpresa('vagas'),
            'area_id' => ['required', 'integer'],
            'quantidade' => ['required'],
            'tipo_contratacao' => ['required'],
            'prioridade' => ['required'],
            'solicitante' => ['required'],
            'outras_informacoes.posicao' => ['required'],
            'outras_informacoes.processo' => ['required'],
            'outras_informacoes.contrato' => ['required'],
            'outras_informacoes.ppra' => ['required'],
            'outras_informacoes.salario' => ['required'],
            'outras_informacoes.beneficio' => ['required'],
            'outras_informacoes.gestor_id' => $this->regraIdNaEmpresa('users'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao alterar Solicitação de vaga',
            'erros' => $validator->errors(),
        ], 400));
    }
}
