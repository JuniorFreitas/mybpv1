<?php

namespace App\Http\Requests\Entrevista;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateParecerRhRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'curriculo.municipio_id' => ['required'],
            'cliente_id' => ['required'],
            'vaga_id' => $this->regraIdNaEmpresa('vagas'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao alterar a Entrevista',
            'erros' => $validator->errors(),
        ], 400));
    }
}
