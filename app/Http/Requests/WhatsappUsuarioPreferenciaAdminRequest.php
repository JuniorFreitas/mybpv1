<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WhatsappUsuarioPreferenciaAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();

        return $user && (
            $user->can('configuracao_whatsapp')
            || $user->can('administracao_clientes')
        );
    }

    public function rules(): array
    {
        return [
            'modulo' => 'required|string|max:80',
            'receber' => 'required|boolean',
        ];
    }
}
