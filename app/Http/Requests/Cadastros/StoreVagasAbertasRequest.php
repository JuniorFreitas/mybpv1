<?php

namespace App\Http\Requests\Cadastros;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreVagasAbertasRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cadastro_vagas_abertas_insert');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->input('ativo') == 'true',
            'ativo_sistema' => $this->input('ativo_sistema') == 'true',
        ]);
    }

    public function rules(): array
    {
        return [
            'vaga_id' => $this->regraIdNaEmpresa('vagas'),
            'municipio_id' => $this->regraIdAtivo('municipios'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar Vaga',
            'erros' => $validator->errors(),
        ], 400));
    }
}
