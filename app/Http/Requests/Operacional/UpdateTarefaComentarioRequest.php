<?php

namespace App\Http\Requests\Operacional;

use App\Models\TarefaComentario;
use App\Support\WeeklyReportHtml;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTarefaComentarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'comentario' => WeeklyReportHtml::sanitize($this->input('comentario')),
        ]);
    }

    public function rules(): array
    {
        return [
            'comentario' => ['required', 'string', 'min:1', 'max:20000'],
            'tipo' => ['nullable', 'in:' . implode(',', TarefaComentario::TIPOS)],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if (WeeklyReportHtml::isEmpty((string) $this->input('comentario'))) {
            throw new HttpResponseException(response()->json([
                'msg' => 'Informe o texto do comentário',
                'erros' => ['comentario' => ['O comentário é obrigatório.']],
            ], 400));
        }

        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao atualizar o comentário',
            'erros' => $validator->errors(),
        ], 400));
    }
}
