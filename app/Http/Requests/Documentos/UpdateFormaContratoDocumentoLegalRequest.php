<?php

namespace App\Http\Requests\Documentos;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Rules\TenantUniqueRules;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateFormaContratoDocumentoLegalRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('administracao_documentos_legais_insert');
    }

    public function rules(): array
    {
        return [
            'titulo' => [
                'required',
                'min:3',
                new TenantUniqueRules('documentos_legais_forma_contrato', $this->segment(5)),
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar Forma Contrato',
            'erros' => $validator->errors(),
        ], 400));
    }
}
