<?php

namespace App\Http\Requests\Admissao;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CadastraMassaAdmissaoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'selecionados' => ['required', 'array', 'min:1'],
            'selecionados.*' => ['integer', 'min:1'],
            'tipo_admissao' => ['required', 'string'],
            'status' => ['required', 'string'],
            'data_admissao' => ['sometimes', 'nullable', 'string'],
            'prazo_experiencia' => ['sometimes', 'nullable', 'string'],
            'data_encerramento' => ['sometimes', 'nullable', 'string'],
            'documento_portaria' => ['sometimes', 'nullable', 'string'],
            'status_carteira_treinamento' => ['sometimes', 'nullable', 'string'],
            'padrao_treinamento_id' => $this->regraIdNaEmpresa('padroes_treinamento', required: false, nullable: true),
            'data_entrega_area' => ['sometimes', 'nullable', 'string'],
            'biometria' => ['sometimes', 'nullable'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar admissão em massa',
            'erros' => $validator->errors(),
        ], 400));
    }
}
