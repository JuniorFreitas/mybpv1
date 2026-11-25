<?php

namespace App\Http\Requests\Cadastros;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreProjetoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cadastro_projetos_insert');
    }

    public function rules(): array
    {
        $regra = Rule::unique('projetos')->where(function ($query) {
            return $this->aplicarSoftDeleteSeExistir(
                $query->whereEmpresaId(auth()->user()->empresa_id)
                    ->whereNome($this->input('nome')),
                'projetos'
            );
        });

        return [
            'nome' => ['required', $regra],
            'qnt_total' => 'required|numeric',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar Projeto',
            'erros' => $validator->errors(),
        ], 400));
    }
}
