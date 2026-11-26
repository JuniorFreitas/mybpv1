<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePapelRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'empresa_id' => $this->empresaIdAutenticada(),
            'ativo' => filter_var($this->input('ativo'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                ?? ($this->input('ativo') === 'true' || $this->input('ativo') === true),
            'master' => false,
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'min:3'],
            'descricao' => ['required', 'min:3'],
            'ativo' => ['required', 'boolean'],
            'empresa_id' => ['required', 'integer', 'in:' . $this->empresaIdAutenticada()],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao alterar papel',
            'erros' => $validator->errors(),
        ], 400));
    }
}
