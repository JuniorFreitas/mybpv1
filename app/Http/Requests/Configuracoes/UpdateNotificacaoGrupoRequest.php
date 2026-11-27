<?php

namespace App\Http\Requests\Configuracoes;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Models\NotificacaoTipo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificacaoGrupoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return $this->user()?->can('configuracao_notificacoes') ?? false;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:160'],
            'ativo' => ['sometimes', 'boolean'],
            'escopo_cc' => ['required', 'string', Rule::in(NotificacaoTipo::ESCOPOS_CC)],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', $this->existsNaEmpresa('users')],
            'centro_custo_ids' => ['nullable', 'array'],
            'centro_custo_ids.*' => ['integer', $this->existsNaEmpresa('centro_custos')],
        ];
    }
}
