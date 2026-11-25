<?php

namespace App\Http\Requests\Avaliacao;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreAvaliacaoTipoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $nome = $this->input('nome');
        $descricao = $this->input('descricao');
        $empresaId = $this->empresaIdAutenticada();

        return [
            'nome' => [
                'required',
                'min:3',
                Rule::unique('avaliacoes_tipos')->where(function ($query) use ($nome, $descricao, $empresaId) {
                    return $this->aplicarSoftDeleteSeExistir(
                        $query->where('nome', $nome)
                            ->where('descricao', $descricao)
                            ->where('empresa_id', $empresaId),
                        'avaliacoes_tipos'
                    );
                }),
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar tipo de avaliação',
            'erros' => $validator->errors(),
        ], 400));
    }
}
