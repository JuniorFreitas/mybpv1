<?php

namespace App\Http\Requests\Operacional;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Models\Cloud;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Form Request do CloudController::updateCloud() — atualização do cadastro do Cloud (espaço).
 */
class UpdateCloudCadastroRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $nome = $this->input('nome');
        /** @var Cloud $cloud */
        $cloud = $this->route('cloud');

        return [
            'nome' => [
                'required',
                Rule::unique('clouds')->ignore($cloud->id)->where(function ($query) use ($nome) {
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
            'msg' => 'Erro ao atualizar o Cloud',
            'erros' => $validator->errors(),
        ], 400));
    }
}
