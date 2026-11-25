<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEmpresaTemporariaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cadastro_empresa_temporaria_update');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->input('ativo') == 'true',
        ]);
    }

    public function rules(): array
    {
        return [
            'razao_social' => 'required',
            'dados.cnpj' => 'required|min:18',
            'dados.email' => 'required|email:rfc,dns',
            'ativo' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao alterar a Empresa',
            'erros' => $validator->errors(),
        ], 400));
    }
}
