<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUsuarioApiRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->tokenCan('usuario_create');
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
        return [
            'nome' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'tipo' => 'required',
            'papel_id' => $this->regraIdNaEmpresa('papeis'),
            'telefone' => 'required|unique:users|max:14',
            'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:6',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json($validator->messages(), 400));
    }
}
