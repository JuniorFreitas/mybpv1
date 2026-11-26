<?php

namespace App\Http\Requests\PromocaoFeedback;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePromocaoFeedbackRequest extends FormRequest
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
            'novo_cargo' => ['required'],
            'novo_salario' => ['required'],
            'motivo' => ['required'],
            'percentual' => ['required', 'numeric', 'gt:0'],
            'tipo' => ['required'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Salvar a Promoção',
            'erros' => $validator->errors(),
        ], 400));
    }
}
