<?php

namespace App\Http\Requests\Afastamento;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAfastamentoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'afastamento' => ['required', 'array', 'min:1'],
            'afastamento.*.feedback_id' => ['nullable', 'integer', $this->existsNaEmpresa('feedback_curriculos')],
            'afastamento.*.motivo' => ['required'],
            'afastamento.*.periodo' => ['required'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Salvar Afastamento',
            'erros' => $validator->errors(),
        ], 400));
    }
}
