<?php

namespace App\Http\Requests\Entrevista;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateParecerRotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'tem_rota' => ['sometimes', 'boolean'],
            'pega_onibus' => ['nullable', 'boolean'],
            'vale_transporte' => ['sometimes', 'boolean'],
            'rota_atende' => ['sometimes', 'boolean'],
            'rota_tipo' => ['sometimes', 'string', 'max:100'],
            'quem_entrevistou' => ['sometimes', 'string', 'min:3', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'tem_rota.boolean' => 'Campo tem rota deve ser verdadeiro ou falso',
            'vale_transporte.boolean' => 'Campo vale transporte deve ser verdadeiro ou falso',
            'rota_atende.boolean' => 'Campo rota atende deve ser verdadeiro ou falso',
            'rota_tipo.max' => 'Tipo de rota deve ter no máximo 100 caracteres',
            'quem_entrevistou.min' => 'Nome do entrevistador deve ter pelo menos 3 caracteres',
            'quem_entrevistou.max' => 'Nome do entrevistador deve ter no máximo 255 caracteres',
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
