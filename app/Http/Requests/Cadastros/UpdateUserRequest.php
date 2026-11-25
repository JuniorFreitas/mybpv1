<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('usuario_usuarios_update');
    }

    protected function prepareForValidation(): void
    {
        $merge = [
            'ativo' => $this->input('ativo') == 'true',
        ];

        if ($this->filled('login')) {
            $merge['login'] = strtolower(trim((string) $this->input('login')));
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|min:3',
            'login' => 'required|email:rfc,dns',
            'ativo' => 'required|boolean',
            'tipo' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao atualizar os dados do usuário',
            'erros' => $validator->errors(),
        ], 400));
    }
}
