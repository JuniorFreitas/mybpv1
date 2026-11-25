<?php

namespace App\Http\Requests\Operacional;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTarefaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'titulo' => ['sometimes', 'min:1'],
            'concluido' => ['sometimes', 'boolean'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao atualizar a tarefa',
            'erros' => $validator->errors(),
        ], 400));
    }
}
