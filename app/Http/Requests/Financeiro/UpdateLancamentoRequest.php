<?php

namespace App\Http\Requests\Financeiro;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateLancamentoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'data_hora' => ['required', 'min:19', 'date_format:d/m/Y à\s H:i'],
            'plano_id' => ['required', 'integer', $this->existsNaEmpresa('plano_contas')],
            'operacao' => ['required', 'in:C,D,T'],
            'formas.*.id' => ['required', 'integer', $this->existsNaEmpresa('formas_pagamento')],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao alterar o lançamento',
            'erros' => $validator->errors(),
        ], 400));
    }
}
