<?php

namespace App\Http\Requests\Cadastros;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreFormaPagamentoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('financeiro_formas-pagamento_insert');
    }

    public function rules(): array
    {
        return [
            'descricao' => ['required', 'min:3',
                Rule::unique('formas_pagamento')->where(function ($query) {
                    return $this->aplicarSoftDeleteSeExistir(
                        $query->whereDescricao($this->input('descricao'))->whereEmpresaId(auth()->user()->empresa_id),
                        'formas_pagamento'
                    );
                })],
            'ativo' => 'required|boolean',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar uma nova classificação',
            'erros' => $validator->errors(),
        ], 400));
    }
}
