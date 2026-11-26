<?php

namespace App\Http\Requests\Operacional;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOcorrenciaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'assunto' => ['required', 'min:2'],
            'setor_id' => $this->regraIdNaEmpresa('ocorrencias_setores'),
            'tag_id' => $this->regraIdNaEmpresa('tags', required: false, nullable: true),
            'tipo' => ['required'],
            'resposta' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'setor_id.exists' => 'Setor não cadastrado.',
            'tag_id.exists' => 'Tag não cadastrada.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Cadastrar',
            'erros' => $validator->errors(),
        ], 400));
    }
}
