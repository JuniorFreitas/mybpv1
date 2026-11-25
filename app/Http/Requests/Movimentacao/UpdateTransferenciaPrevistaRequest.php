<?php

namespace App\Http\Requests\Movimentacao;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTransferenciaPrevistaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $origem = $this->input('centro_custo_origem_id');
        $this->merge([
            'centro_custo_origem_id' => ($origem === null || $origem === '') ? null : (int) $origem,
        ]);
    }

    public function rules(): array
    {
        return [
            'centro_custo_origem_id' => $this->regraIdNaEmpresa('centro_custos'),
            'centro_custo_destino_id' => $this->regraIdNaEmpresa('centro_custos', required: false, nullable: true),
            'colaborador_id' => $this->regraIdNaEmpresa('users'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao atualizar Solicitação de Transferência',
            'erros' => $validator->errors(),
        ], 400));
    }
}
