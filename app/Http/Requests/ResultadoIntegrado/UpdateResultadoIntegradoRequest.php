<?php

namespace App\Http\Requests\ResultadoIntegrado;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateResultadoIntegradoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'feedback_id' => $this->regraIdNaEmpresa('feedback_curriculos'),
            'centro_custo_id' => ['nullable', 'integer', $this->existsNaEmpresa('centro_custos')],
            'documentos_entregue' => ['required'],
            'encaminhado_exame' => ['required'],
            'encaminhado_treinamento' => ['required'],
            'responsavel_envio' => ['required', 'min:3'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao alterar o resultado integrado',
            'erros' => $validator->errors(),
        ], 400));
    }
}
