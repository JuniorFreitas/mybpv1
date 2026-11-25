<?php

namespace App\Http\Requests\AprovacaoExtra;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAprovacaoExtraConfigRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nome_aprovacao' => 'required|string|max:255',
            'usuarios_autorizados' => 'nullable|array',
            'usuarios_autorizados.*' => $this->regraIdNaEmpresa('users', required: false),
            'ativo' => 'required|boolean',
        ];
    }
}
