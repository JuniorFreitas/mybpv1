<?php

namespace App\Http\Requests\Cadastros;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBeneficioFeedbackRequest extends FormRequest
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
            'beneficio_id' => $this->regraIdNaEmpresa('beneficios'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Salvar as Notas',
            'erros' => $validator->errors(),
        ], 400));
    }
}
