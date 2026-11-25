<?php

namespace App\Http\Requests\Financeiro;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class MudarStatusLancamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'boolean'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $lancamento = $this->route('lancamento');

        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao mudar status o lançamento',
            'erros' => $validator->errors(),
            'lancamento' => $lancamento,
        ], 400));
    }
}
