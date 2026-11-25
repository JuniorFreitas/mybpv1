<?php

namespace App\Http\Requests\Operacional;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class MudarSetorOcorrenciaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'setor_id' => $this->regraIdNaEmpresa('ocorrencias_setores'),
        ];
    }

    public function messages(): array
    {
        return [
            'setor_id.exists' => 'Setor não cadastrado.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Mudar Setor',
            'erros' => $validator->errors(),
        ], 400));
    }
}
