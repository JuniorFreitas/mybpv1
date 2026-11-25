<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Município é catálogo global (IBGE): não aplica tenant (sem empresa_id).
 */
class StoreMunicipioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('municipios_insert');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'capital' => $this->input('capital') == 'true',
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|min:3',
            'uf' => 'required|min:2',
            'capital' => 'required|boolean',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar municipio',
            'erros' => $validator->errors(),
        ], 400));
    }
}
