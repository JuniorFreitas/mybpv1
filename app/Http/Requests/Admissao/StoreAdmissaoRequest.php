<?php

namespace App\Http\Requests\Admissao;

use App\Models\Admissao;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAdmissaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'feedback.vaga_id' => ['required', 'integer', 'min:1'],
            'curriculo.cpf' => ['required', 'string'],
            'curriculo.nome' => ['required', 'string'],
            'curriculo.email' => ['sometimes', 'nullable', 'string'],
            'curriculo.telefones' => ['required', 'array', 'min:1'],
            'admissao.status' => ['required', 'in:' . implode(',', Admissao::TODOS_STATUS_ADMISSAO)],
            'admissao.dados_admissoes' => ['required', 'array'],
            'resultado_integrado' => ['required', 'array'],
            'parecer_rh' => ['required', 'array'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar Admissão',
            'erros' => $validator->errors(),
        ], 400));
    }
}
