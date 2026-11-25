<?php

namespace App\Http\Requests\Cadastros;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateDepartamentoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        // Controller original não possuía authorize() específico no update();
        // a autorização é garantida pelo middleware de rota `can:cadastro_departamento`.
        return auth()->check();
    }

    public function rules(): array
    {
        $ignoreId = (int) $this->route('departamento');

        $regra = Rule::unique('departamentos')->where(function ($query) {
            return $this->aplicarSoftDeleteSeExistir(
                $query->whereEmpresaId(auth()->user()->empresa_id)
                    ->whereLabel($this->input('label')),
                'departamentos'
            );
        })->ignore($ignoreId);

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
