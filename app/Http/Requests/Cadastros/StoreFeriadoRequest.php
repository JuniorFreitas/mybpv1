<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreFeriadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('controle_ponto_feriados_insert');
    }

    public function rules(): array
    {
        return [
            'descricao' => 'required|min:3',
            'data' => 'required',
            'ativo' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar feriado',
            'erros' => $validator->errors(),
        ], 400));
    }
}
