<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreHabilidadeApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->tokenCan('habilidade_create');
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
            'nome' => 'required|unique:habilidades|max:255',
            'descricao' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json($validator->messages(), 400));
    }
}
