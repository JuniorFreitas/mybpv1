<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('usuario_usuarios_insert');
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('login')) {
            $this->merge([
                'login' => strtolower(trim((string) $this->input('login'))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|min:3',
            'login' => 'required|email:rfc,dns',
            'tipo' => 'required',
            'ativo' => 'required|boolean',
            // empresa_id não é restrito ao tenant autenticado: usuários MyBPIN
            // (grupo master) cadastram usuários para qualquer empresa cliente.
            'empresa_id' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar usuário',
            'erros' => $validator->errors(),
        ], 400));
    }
}
