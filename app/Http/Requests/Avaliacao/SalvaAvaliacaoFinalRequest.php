<?php

namespace App\Http\Requests\Avaliacao;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SalvaAvaliacaoFinalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'planos_acoes' => ['required', 'array'],
            'planos_acoes.*.topico_id' => ['required'],
            'planos_acoes.*.plano_de_acao' => ['required'],
            'planos_acoes.*.responsavel' => ['required'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($validator->errors()->has('planos_acoes')) {
            throw new HttpResponseException(response()->json([
                'msg' => 'ERRO: É necessário inserir um plano de ação',
            ], 400));
        }

        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao salvar a avaliação final',
            'erros' => $validator->errors(),
        ], 400));
    }
}
