<?php

namespace App\Http\Requests\Documentos;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDossieTipoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cadastro_dossie_tipos_insert');
    }

    public function rules(): array
    {
        return [
            'label' => 'required|string|max:255',
            'tipo_modelo' => 'nullable|string|max:100',
            'tipo_documento' => 'nullable|string|max:100',
            'tem_modelo' => 'nullable|boolean',
            'permite_assinatura' => 'required|boolean',
            'ordem' => 'nullable|integer|min:0',
            'ativo' => 'required|boolean',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar tipo de dossiê',
            'erros' => $validator->errors(),
        ], 400));
    }
}
