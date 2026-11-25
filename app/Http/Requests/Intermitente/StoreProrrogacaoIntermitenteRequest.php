<?php

namespace App\Http\Requests\Intermitente;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProrrogacaoIntermitenteRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'intermitente_id' => $this->regraIdNaEmpresa('intermitentes'),
            // Mantidas as chaves originais (bug pré-existente: não usam a sintaxe de wildcard
            // "prorrogacao.*.campo"; ver nota em TD-004.md).
            'prorrogacao*data_inicio' => ['required'],
            'prorrogacao*data_fim' => ['required'],
            'prorrogacao*solicitante' => ['required'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Cadastrar Prorrogação',
            'erros' => $validator->errors(),
        ], 400));
    }
}
