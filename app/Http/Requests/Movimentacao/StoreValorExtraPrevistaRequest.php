<?php

namespace App\Http\Requests\Movimentacao;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreValorExtraPrevistaRequest extends FormRequest
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
            'colaborador_id' => $this->regraIdNaEmpresa('users'),
            'centro_custo_filial_id' => $this->regraIdNaEmpresa('centro_custo_filials', required: false, nullable: true),
            'tipo' => ['required'],
            'periodo_dias' => ['required'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Solicitar Valor Extra',
            'erros' => $validator->errors(),
        ], 400));
    }
}
