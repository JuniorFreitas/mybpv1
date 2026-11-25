<?php

namespace App\Http\Requests\Operacional;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Models\OcorrenciaJornada;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateOcorrenciaJornadaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $dados = $this->all();
        /** @var OcorrenciaJornada $ocorrenciaJornada */
        $ocorrenciaJornada = $this->route('ocorrencia_jornada');

        return [
            'descricao' => [
                'required',
                'min:3',
                Rule::unique('ocorrencias_jornada')->where(function ($query) use ($dados, $ocorrenciaJornada) {
                    return $this->aplicarSoftDeleteSeExistir(
                        $query->whereDescricao($dados['descricao'] ?? null)
                            ->whereEmpresaId(auth()->user()->empresa_id)
                            ->whereNotIn('id', [$ocorrenciaJornada->id]),
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
            'msg' => 'Erro ao atualizar a classificação de plano de conta',
            'erros' => $validator->errors(),
        ], 400));
    }
}
