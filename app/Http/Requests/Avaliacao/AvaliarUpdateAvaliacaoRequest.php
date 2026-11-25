<?php

namespace App\Http\Requests\Avaliacao;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AvaliarUpdateAvaliacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'respostas' => ['required', 'array'],
            'comentario' => ['nullable'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $respostas = collect($this->input('respostas'))->collapse()->all();
            foreach ($respostas as $resposta) {
                $nota = $resposta['nota'] ?? null;
                if ($nota === null || $nota === '' || !is_numeric($nota) || (float) $nota < 1 || (float) $nota > 5) {
                    $validator->errors()->add('respostas', 'Verifique as respostas, existe pergunta sem nota');
                    break;
                }
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Verifique as respostas, existe pergunta sem nota',
        ], 400));
    }
}
