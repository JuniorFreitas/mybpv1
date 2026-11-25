<?php

namespace App\Http\Requests\Financeiro;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateCategoriaPlanoContaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $empresaId = $this->empresaIdAutenticada();
        $categoriaId = (int) ($this->route('categoria')?->id ?? $this->route('categoria'));

        return [
            'descricao' => [
                'required',
                'min:3',
                Rule::unique('categoria_plano_contas')->where(fn ($query) => $this->aplicarSoftDeleteSeExistir(
                    $query
                        ->where('descricao', $this->input('descricao'))
                        ->where('empresa_id', $empresaId)
                        ->whereNotIn('id', [$categoriaId]),
                    'categoria_plano_contas'
                )),
            ],
            'ativo' => ['required', 'boolean'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao atualizar a classificação de plano de conta',
            'erros' => $validator->errors(),
        ], 400));
    }
}
