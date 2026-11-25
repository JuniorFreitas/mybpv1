<?php

namespace App\Http\Requests\Cih;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AprovarCihRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        if (is_null($this->input('resposta_rh'))) {
            return [
                'status' => ['required'],
            ];
        }

        return [
            'resposta_rh' => ['required'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Salvar Informações',
            'erros' => $validator->errors(),
        ], 400));
    }
}
