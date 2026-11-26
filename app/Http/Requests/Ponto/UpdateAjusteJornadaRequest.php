<?php

namespace App\Http\Requests\Ponto;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAjusteJornadaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'periodos' => 'required',
            // Padrões globais (empresa_id null) + da empresa; bloqueia outras empresas
            'ocorrencia_id' => $this->regraIdNaEmpresaOuGlobal('ocorrencias_jornada'),
            'periodos.*.entrada' => 'required|min:5',
            'periodos.*.saida' => 'required|min:5',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao registrar o ponto',
            'erros' => $validator->errors(),
        ], 400));
    }
}
