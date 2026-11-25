<?php

namespace App\Http\Requests\Operacional;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreGaleriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->input('ativo') == 'true',
            'descricao' => '',
        ]);
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'min:1', 'unique:galerias,titulo'],
            'ativo' => ['required'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar Galeria',
            'erros' => $validator->errors(),
        ], 400));
    }
}
