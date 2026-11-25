<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePerimetroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('controle_ponto_perimetros_insert');
    }

    public function rules(): array
    {
        return [
            'descricao' => 'required|min:3',
            'lat' => 'numeric',
            'long' => 'numeric',
            'perimetro' => 'numeric',
            'obrigatorio' => 'required|boolean',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao salvar o perímetro',
            'erros' => $validator->errors(),
        ], 400));
    }
}
