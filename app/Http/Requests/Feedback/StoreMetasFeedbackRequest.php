<?php

namespace App\Http\Requests\Feedback;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMetasFeedbackRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $rules = [
            'nome' => 'required',
            'descricao' => 'required',
            'data_fim' => 'required',
            'data_inicio' => 'required',
        ];

        if ($this->filled('feedback_id')) {
            $rules['feedback_id'] = $this->regraIdNaEmpresa('feedback_curriculos');
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Salvar a Meta',
            'erros' => $validator->errors(),
        ], 400));
    }
}
