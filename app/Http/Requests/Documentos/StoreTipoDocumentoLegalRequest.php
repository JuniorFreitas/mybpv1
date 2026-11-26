<?php

namespace App\Http\Requests\Documentos;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreTipoDocumentoLegalRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('administracao_documentos_legais_insert');
    }

    public function rules(): array
    {
        return [
            'nome' => [
                'required',
                'min:3',
                Rule::unique('tipo_documentos')->where(function ($query) {
                    return $this->aplicarSoftDeleteSeExistir(
                        $query->where('nome', $this->input('nome'))
                            ->where('tipo', $this->input('tipo'))
                            ->where('empresa_id', $this->empresaIdAutenticada()),
                        'tipo_documentos'
                    );
                }),
            ],
            'tipo' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar Tipo Documento',
            'erros' => $validator->errors(),
        ], 400));
    }
}
