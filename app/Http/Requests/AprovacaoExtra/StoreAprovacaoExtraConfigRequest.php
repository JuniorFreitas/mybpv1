<?php

namespace App\Http\Requests\AprovacaoExtra;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Models\AprovacaoExtraConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAprovacaoExtraConfigRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $tipos = array_keys(AprovacaoExtraConfig::TIPOS_PROCESSO);

        return [
            'tipo_processo' => ['required', Rule::in($tipos)],
            'nome_aprovacao' => 'required|string|max:255',
            'usuarios_autorizados' => 'nullable|array',
            'usuarios_autorizados.*' => $this->regraIdNaEmpresa('users', required: false),
            'ativo' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_processo.required' => 'Informe o tipo de processo.',
            'tipo_processo.in' => 'Tipo de processo inválido.',
            'nome_aprovacao.required' => 'Informe o nome da aprovação.',
        ];
    }
}
