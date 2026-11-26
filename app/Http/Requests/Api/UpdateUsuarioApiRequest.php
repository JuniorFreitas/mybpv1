<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUsuarioApiRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->tokenCan('usuario_update');
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Acesso negado',
            'error' => true,
        ], 403));
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');
        $usuarioId = is_object($usuario) ? $usuario->id : $usuario;

        return [
            'nome' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $usuarioId,
            'tipo' => 'required',
            'papel_id' => $this->regraIdNaEmpresa('papeis'),
            'telefone' => 'required|max:14|unique:users,telefone,' . $usuarioId,
            'alterar_senha' => 'boolean',
            'password' => 'sometimes|min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'sometimes|min:6',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json($validator->messages(), 400));
    }
}
