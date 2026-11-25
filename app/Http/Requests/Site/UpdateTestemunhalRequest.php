<?php

namespace App\Http\Requests\Site;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTestemunhalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->input('ativo') == 'true' || $this->input('ativo') === true,
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => 'required',
            'texto' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao atualizar Depoimento',
            'erros' => $validator->errors(),
        ], 400));
    }
}
