<?php

namespace App\Http\Requests\Admissao;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Models\Admissao;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAdmissaoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'vagas_abertas_id' => $this->regraIdNaEmpresa('vagas_abertas'),
            'curriculo.email' => ['required', 'email:rfc,dns'],
            'curriculo.telefones' => ['required', 'array', 'min:1', 'tel_principal_mark'],
            'admissao.status' => ['required', 'in:' . implode(',', Admissao::TODOS_STATUS_ADMISSAO)],
            'admissao.status_carteira_treinamento' => ['sometimes', 'nullable', 'string', 'in:' . implode(',', Admissao::TODOS_STATUS_CARTEIRA_TREINAMETO)],
            'admissao.padrao_treinamento_id' => $this->regraIdNaEmpresa('padroes_treinamento', required: false, nullable: true),
            'admissao.matricula' => ['sometimes', 'nullable', 'string', 'max:255'],
            'admissao.data_admissao' => ['sometimes', 'nullable', 'string'],
            'admissao.centro_custo_id' => $this->regraIdNaEmpresa('centro_custos', required: false, nullable: true),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Salvar Admissão',
            'erros' => $validator->errors(),
        ], 400));
    }
}
