<?php

namespace App\Http\Requests\Treinamentos;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEmpresaExameRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();
        $input['ativo'] = $input['ativo'] == 'true';

        $this->replace($input);
    }

    public function rules(): array
    {
        return [
            'nome' => ['required'],
            'dados.cnpj' => ['required', 'min:18'],
            'dados.email' => ['required', 'email:rfc,dns'],
            'ativo' => ['required'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao alterar a Empresa',
            'erros' => $validator->errors(),
        ], 400));
    }
}
