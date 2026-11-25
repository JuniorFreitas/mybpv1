<?php

namespace App\Http\Requests\Operacional;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Form Request do CloudController::storeCloud() — cadastro do Cloud (espaço) por empresa.
 */
class StoreCloudCadastroRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $nome = $this->input('nome');

        return [
            'nome' => [
                'required',
                Rule::unique('clouds')->where(function ($query) use ($nome) {
                    return $this->aplicarSoftDeleteSeExistir(
                        $query->whereNome($nome)->whereEmpresaId(auth()->user()->empresa_id),
                        'clouds'
                    );
                }),
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar o Cloud',
            'erros' => $validator->errors(),
        ], 400));
    }
}
