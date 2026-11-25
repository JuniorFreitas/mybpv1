<?php

namespace App\Http\Requests\Operacional;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreQuadroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'titulo' => trim((string) $this->input('titulo', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'min:1', 'max:120'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao criar o quadro',
            'erros' => $validator->errors(),
        ], 400));
    }
}
