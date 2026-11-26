<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePerfilUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (int) auth()->id() === (int) $this->route('id');
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|min:3',
            'login' => 'required|min:3',
            'foto_perfil' => 'sometimes|array',
            'foto_perfilDel' => 'sometimes|array',
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
