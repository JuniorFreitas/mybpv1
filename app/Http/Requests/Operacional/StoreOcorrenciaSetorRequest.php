<?php

namespace App\Http\Requests\Operacional;

use App\Models\OcorrenciaSetor;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOcorrenciaSetorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $nome = trim((string) $this->input('nome'));
        $nome = preg_replace('/\s+/u', ' ', $nome) ?? $nome;
        $this->merge(['nome' => $nome]);
    }

    public function rules(): array
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        return [
            'nome' => [
                'required',
                'min:1',
                function ($attribute, $value, $fail) use ($empresaId) {
                    $existe = OcorrenciaSetor::query()
                        ->where('empresa_id', $empresaId)
                        ->whereRaw('LOWER(TRIM(nome)) = ?', [mb_strtolower((string) $value, 'UTF-8')])
                        ->exists();
                    if ($existe) {
                        $fail('Já existe um setor com este nome para esta empresa.');
                    }
                },
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Cadastrar',
            'erros' => $validator->errors(),
        ], 400));
    }
}
