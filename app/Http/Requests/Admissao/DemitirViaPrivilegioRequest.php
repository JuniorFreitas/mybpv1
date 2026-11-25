<?php

namespace App\Http\Requests\Admissao;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DemitirViaPrivilegioRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'admissao_id' => ['required', 'integer', 'min:1'],
            'feedback_id' => $this->regraIdNaEmpresa('feedback_curriculos'),
            'curriculo_id' => ['required', 'integer', 'min:1'],
            'data_desmobilizacao' => ['required', 'string'],
            'motivo_rescisao_id' => ['required', 'integer', 'min:1'],
            'tipo_aviso_id' => ['required', 'integer', 'min:1'],
            'solicitado_por' => ['required', 'string', 'max:255'],
            'user_id' => $this->regraIdNaEmpresa('users'),
            'cipa' => ['sometimes', 'nullable'],
            'comentario' => ['sometimes', 'nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao demitir via privilégio',
            'erros' => $validator->errors(),
        ], 400));
    }
}
