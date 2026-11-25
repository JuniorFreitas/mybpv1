<?php

namespace App\Http\Requests\Avaliacao;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAvaliacaoTopicoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'topico' => ['required'],
            'avaliacao_tipo_id' => $this->regraIdNaEmpresa('avaliacoes_tipos'),
            'subtopico.*.topico' => ['required'],
            'ativo' => ['required'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar tópicos de avaliações',
            'erros' => $validator->errors(),
        ], 400));
    }
}
