<?php

namespace App\Http\Requests\Documentos;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDocumentoPreadmissaoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'categoria_id' => ['nullable', 'integer'],
            'categoria_nova' => ['nullable', 'string', 'max:255'],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'ativo' => ['required', 'boolean'],
            'configuracoes' => ['nullable', 'array'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar documento',
            'erros' => $validator->errors(),
        ], 400));
    }
}
