<?php

namespace App\Http\Requests\Treinamentos;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StorePcmsoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $regra = Rule::unique('pcmsos')->where(
            fn ($query) => $this->aplicarSoftDeleteSeExistir(
                $query->whereEmpresaId($this->empresaIdAutenticada())->whereLabel($this->input('label')),
                'pcmsos'
            )
        );

        return [
            'label' => ['required', $regra],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar PCMSO',
            'erros' => $validator->errors(),
        ], 400));
    }
}
