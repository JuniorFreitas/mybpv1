<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEmpresaEscalaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('controle_ponto_escalas_update');
    }

    public function rules(): array
    {
        return [
            'descricao' => 'required|min:3',
            'inicio' => 'required|min:10',
            'jornadas' => 'required|array|min:1',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao salvar a escala',
            'erros' => $validator->errors(),
        ], 400));
    }
}
