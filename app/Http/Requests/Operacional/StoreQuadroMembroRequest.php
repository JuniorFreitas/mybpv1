<?php

namespace App\Http\Requests\Operacional;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreQuadroMembroRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'user_id' => $this->regraIdNaEmpresa('users'),
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Informe um usuário válido',
            'user_id.integer' => 'Informe um usuário válido',
            'user_id.exists' => 'Informe um usuário válido',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Informe um usuário válido',
            'erros' => $validator->errors(),
        ], 400));
    }
}
