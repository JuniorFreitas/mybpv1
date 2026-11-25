<?php

namespace App\Http\Requests\Historico;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMedidasAdministrativasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        // Regras originais permanecem comentadas no controller (sem campos obrigatórios hoje).
        return [];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Salvar Informações',
            'erros' => $validator->errors(),
        ], 400));
    }
}
