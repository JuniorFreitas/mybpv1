<?php

namespace App\Http\Requests\Entrevista;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreParecerTestePraticoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'feedback_id' => ['nullable', 'integer', $this->existsNaEmpresa('feedback_curriculos')],
            'fez_teste' => ['required'],
            'parecer_final_teste' => ['required'],
            'quem_entrevistou' => ['required', 'min:3'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao alterar a entrevista',
            'erros' => $validator->errors(),
        ], 400));
    }
}
