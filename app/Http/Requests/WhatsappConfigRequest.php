<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WhatsappConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nome_exibicao' => 'nullable|string|max:255',
            'telefone_contato' => 'nullable|string|max:30',
            'endereco_completo' => 'nullable|string|max:2000',
            'texto_assinatura' => 'nullable|string|max:2000',
            'modulos_habilitados' => 'nullable|array',
            'modulos_habilitados.*.modulo' => 'required_with:modulos_habilitados|string|max:80',
            'modulos_habilitados.*.habilitado' => 'required_with:modulos_habilitados|boolean',
        ];
    }
}
