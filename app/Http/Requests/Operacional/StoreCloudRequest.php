<?php

namespace App\Http\Requests\Operacional;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Form Request do CloudController::store() — cria uma pasta raiz (ItensCloud).
 */
class StoreCloudRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'label' => [
                'required',
                'min:1',
                Rule::unique('itens_cloud', 'label')->where(function ($query) {
                    return $this->aplicarSoftDeleteSeExistir($query, 'itens_cloud');
                }),
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao criar nova pasta',
            'erros' => $validator->errors(),
        ], 400));
    }
}
