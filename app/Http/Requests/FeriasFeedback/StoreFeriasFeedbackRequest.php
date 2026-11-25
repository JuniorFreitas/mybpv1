<?php

namespace App\Http\Requests\FeriasFeedback;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreFeriasFeedbackRequest extends FormRequest
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
            'ano' => ['required'],
            'valor' => ['required'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Salvar as Férias',
            'erros' => $validator->errors(),
        ], 400));
    }
}
