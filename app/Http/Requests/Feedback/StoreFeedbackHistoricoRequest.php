<?php

namespace App\Http\Requests\Feedback;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreFeedbackHistoricoRequest extends FormRequest
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
            'situacao' => 'required',
            'descricao' => 'required',
            'compromisso' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Salvar a Feedback',
            'erros' => $validator->errors(),
        ], 400));
    }
}
