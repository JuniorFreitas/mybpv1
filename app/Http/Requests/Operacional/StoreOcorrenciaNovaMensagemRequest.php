<?php

namespace App\Http\Requests\Operacional;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOcorrenciaNovaMensagemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'resposta' => ['required'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Cadastrar',
            'erros' => $validator->errors(),
        ], 400));
    }
}
