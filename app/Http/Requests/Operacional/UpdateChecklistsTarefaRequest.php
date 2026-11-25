<?php

namespace App\Http\Requests\Operacional;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateChecklistsTarefaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'titulo' => ['nullable', 'min:1'],
            'datahora_entrega' => ['nullable', 'string'],
            'acao' => ['nullable', 'in:add,remove'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao atualizar a checklist',
            'erros' => $validator->errors(),
        ], 400));
    }
}
