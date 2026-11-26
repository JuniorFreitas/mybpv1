<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateFeriadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('controle_ponto_feriados_update');
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
            'msg' => 'Erro ao atualizar a feriado',
            'erros' => $validator->errors(),
        ], 400));
    }
}
