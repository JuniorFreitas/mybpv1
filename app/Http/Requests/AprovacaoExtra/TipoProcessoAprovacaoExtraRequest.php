<?php

namespace App\Http\Requests\AprovacaoExtra;

use Illuminate\Foundation\Http\FormRequest;

class TipoProcessoAprovacaoExtraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'tipo_processo' => 'required|string|max:100',
        ];
    }
}
