<?php

namespace App\Http\Requests\Recrutamento;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRecrutamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'curriculos' => ['required', 'array'],
            'curriculos.id' => ['required', 'integer', 'min:1'],
            'curriculos.nome' => ['required', 'string', 'max:255'],
            'curriculos.email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'curriculos.cpf' => ['sometimes', 'nullable', 'string', 'max:20'],
            'curriculos.telefones' => ['sometimes', 'nullable', 'array'],
            'contato_realizado' => ['sometimes'],
            'interesse' => ['sometimes'],
            'tem_provas' => ['sometimes'],
        ];
    }

    public function messages(): array
    {
        return [
            'curriculos.required' => 'Informe os dados do currículo.',
            'curriculos.id.required' => 'Informe o identificador do currículo.',
            'curriculos.nome.required' => 'Informe o nome do candidato.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao atualizar recrutamento',
            'erros' => $validator->errors(),
        ], 400));
    }
}
