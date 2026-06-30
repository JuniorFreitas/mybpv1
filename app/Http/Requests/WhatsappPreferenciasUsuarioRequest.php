<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WhatsappPreferenciasUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('preferencias_notificacao_whatsapp');
    }

    public function rules(): array
    {
        return [
            'preferencias' => 'required|array|min:1',
            'preferencias.*.modulo' => 'required|string|max:80',
            'preferencias.*.receber' => 'required|boolean',
        ];
    }
}
