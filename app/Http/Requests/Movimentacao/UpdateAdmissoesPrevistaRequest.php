<?php

namespace App\Http\Requests\Movimentacao;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Models\Admissao;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateAdmissoesPrevistaRequest extends FormRequest
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
            'tipo_contrato' => ['required', Rule::in(Admissao::TODOS_TIPOS_ADMISSAO)],
            'cargo_id' => $this->regraIdNaEmpresa('vagas'),
            'salario_format' => ['required'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Solicitar Admissão',
            'erros' => $validator->errors(),
        ], 400));
    }
}
