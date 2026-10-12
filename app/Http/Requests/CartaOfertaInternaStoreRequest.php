<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartaOfertaInternaStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'curriculo_id' => 'required|integer',
            'vagas_abertas_id' => 'required|integer',
            'feedback_id' => 'nullable|integer',
            'vaga_projeto_id' => 'nullable|integer',
            'setor' => 'nullable|string|max:150',
            'salario' => 'nullable|string|max:80',
            'data_inicio' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'curriculo_id.required' => 'Selecione o candidato.',
            'vagas_abertas_id.required' => 'Selecione a vaga aberta.',
        ];
    }
}
