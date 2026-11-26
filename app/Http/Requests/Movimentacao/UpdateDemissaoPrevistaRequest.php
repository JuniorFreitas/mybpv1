<?php

namespace App\Http\Requests\Movimentacao;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDemissaoPrevistaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'colaborador_id' => $this->regraIdNaEmpresa('users'),
            'valor_format' => ['required'],
            'centro_custo_id' => $this->regraIdNaEmpresa('centro_custos', required: false, nullable: true),
        ];
    }

    public function messages(): array
    {
        return [
            'colaborador_id.required' => 'Informe o colaborador.',
            'valor_format.required' => 'Informe o valor.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Solicitar Demissão',
            'erros' => $validator->errors(),
        ], 400));
    }
}
