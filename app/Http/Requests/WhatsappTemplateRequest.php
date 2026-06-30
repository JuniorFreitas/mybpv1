<?php

namespace App\Http\Requests;

use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WhatsappTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $tipos = array_map(fn (TipoMensagemWhatsapp $t) => $t->value, TipoMensagemWhatsapp::cases());

        return [
            'corpo' => [
                'required',
                'string',
                'max:' . config('whatsapp_templates.max_corpo_length', 4096),
            ],
            'ativo' => 'nullable|boolean',
            'tipo_mensagem' => ['sometimes', Rule::in($tipos)],
            'contexto' => 'nullable|array',
        ];
    }
}
