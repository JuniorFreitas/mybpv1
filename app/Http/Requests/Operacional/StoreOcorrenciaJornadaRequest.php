<?php

namespace App\Http\Requests\Operacional;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreOcorrenciaJornadaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $dados = $this->all();

        return [
            'descricao' => [
                'required',
                'min:3',
                Rule::unique('ocorrencias_jornada')->where(function ($query) use ($dados) {
                    return $this->aplicarSoftDeleteSeExistir(
                        $query->whereDescricao($dados['descricao'] ?? null)
                            ->whereEmpresaId(auth()->user()->empresa_id),
                        'ocorrencias_jornada'
                    );
                }),
            ],
            'trabalhado' => ['required', 'boolean'],
            'conta_horas' => ['required', 'boolean'],
            'ativo' => ['required', 'boolean'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar uma ocorrência',
            'erros' => $validator->errors(),
        ], 400));
    }
}
