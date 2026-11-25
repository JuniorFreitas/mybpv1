<?php

namespace App\Http\Requests\Operacional;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Models\GrupoCloud;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Form Request do CloudConfiguracaoController::update() — atualização de grupo Cloud (GrupoCloud).
 */
class UpdateCloudConfiguracaoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $nome = $this->input('nome');
        /** @var GrupoCloud $grupocloud */
        $grupocloud = $this->route('grupocloud');

        return [
            'nome' => [
                'required',
                Rule::unique('grupo_clouds')->ignore($grupocloud->id)->where(function ($query) use ($nome) {
                    return $this->aplicarSoftDeleteSeExistir(
                        $query->whereNome($nome)->whereEmpresaId(auth()->user()->empresa_id),
                        'grupo_clouds'
                    );
                }),
            ],
            'descricao' => ['required', 'min:3'],
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
