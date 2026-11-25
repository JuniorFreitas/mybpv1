<?php

namespace App\Http\Requests\Cadastros;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreDepartamentoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cadastro_departamento_insert');
    }

    public function rules(): array
    {
        $regra = Rule::unique('departamentos')->where(function ($query) {
            return $this->aplicarSoftDeleteSeExistir(
                $query->whereEmpresaId(auth()->user()->empresa_id)
                    ->whereLabel($this->input('label')),
                'departamentos'
            );
        });

        return [
            'label' => ['required', $regra],
            'ativo' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Cadastrar Departamento',
            'erros' => $validator->errors(),
        ], 400));
    }
}
