<?php

namespace App\Http\Requests\Financeiro;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StorePlanoContaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $empresaId = $this->empresaIdAutenticada();

        return [
            'descricao' => [
                'required',
                'min:3',
                Rule::unique('plano_contas')->where(fn ($query) => $this->aplicarSoftDeleteSeExistir(
                    $query
                        ->where('descricao', $this->input('descricao'))
                        ->where('empresa_id', $empresaId),
                    'plano_contas'
                )),
            ],
            'operacao' => ['required', 'string', 'min:1'],
            'categoria_plano_id' => ['required', 'numeric', 'min:1'],
            'ativo' => ['required', 'boolean'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar plano de conta',
            'erros' => $validator->errors(),
        ], 400));
    }
}
