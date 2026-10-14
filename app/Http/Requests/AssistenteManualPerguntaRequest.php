<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssistenteManualPerguntaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'pergunta' => 'required|string|min:2|max:500',
            'historico' => 'nullable|array|max:12',
            'historico.*.papel' => 'required|string|in:usuario,assistente',
            'historico.*.texto' => 'required|string|max:4000',
        ];
    }

    public function messages(): array
    {
        return [
            'pergunta.required' => 'Digite a sua dúvida.',
            'pergunta.min' => 'Digite a sua dúvida.',
            'pergunta.max' => 'A dúvida deve ter no máximo 500 caracteres.',
        ];
    }
}
