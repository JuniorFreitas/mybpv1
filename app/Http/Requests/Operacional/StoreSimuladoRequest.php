<?php

namespace App\Http\Requests\Operacional;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSimuladoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required'],
            'ativo' => ['required'],
            'perguntas' => ['required', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'perguntas.required' => 'ERRO: É necessário inserir Questões',
            'perguntas.array' => 'ERRO: É necessário inserir Questões',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($validator->errors()->has('perguntas')) {
            throw new HttpResponseException(response()->json([
                'msg' => 'ERRO: É necessário inserir Questões',
            ], 400));
        }

        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar Simulado',
            'erros' => $validator->errors(),
        ], 400));
    }
}
