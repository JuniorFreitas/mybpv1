<?php

namespace App\Http\Requests\Entrevista;

use App\Models\TelefoneCurriculo;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class EnviarWhatsappParecerRotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'telefone' => ['required', 'string', 'min:10'],
            'tipo' => ['required', 'in:' . TelefoneCurriculo::TIPO_WHATS],
        ];
    }

    public function messages(): array
    {
        return [
            'telefone.required' => 'Informe o telefone WhatsApp do candidato',
            'telefone.min' => 'Telefone inválido',
            'tipo.required' => 'Informe o tipo do telefone',
            'tipo.in' => 'O telefone deve ser do tipo WhatsApp',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao enviar WhatsApp',
            'erros' => $validator->errors(),
        ], 400));
    }
}
