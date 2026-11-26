<?php

namespace App\Http\Requests\Operacional;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Form Request do CloudConfiguracaoController::store() — cadastro de grupo Cloud (GrupoCloud).
 */
class StoreCloudConfiguracaoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->input('ativo') == 'true',
        ]);
    }

    public function rules(): array
    {
        $nome = $this->input('nome');

        return [
            'nome' => [
                Rule::unique('grupo_clouds')->where(function ($query) use ($nome) {
                    return $this->aplicarSoftDeleteSeExistir(
                        $query->whereNome($nome)->whereEmpresaId(auth()->user()->empresa_id),
                        'grupo_clouds'
                    );
                }),
            ],
            'descricao' => ['required', 'min:2'],
            'ativo' => ['required', 'boolean'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao alterar grupo',
            'erros' => $validator->errors(),
        ], 400));
    }
}
