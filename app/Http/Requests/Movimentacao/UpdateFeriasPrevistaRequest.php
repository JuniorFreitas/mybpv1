<?php

namespace App\Http\Requests\Movimentacao;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateFeriasPrevistaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'periodo_aquisitivo_id' => $this->regraIdAtivo('periodos_aquisitivos'),
            'data_saida' => ['required'],
            'data_retorno' => ['required'],
            'ultima_data' => ['required'],
            'qnt_dias' => ['required'],
            'dias_saldo' => ['required'],
            'tem_faltas' => ['required', 'boolean'],
            'qnt_faltas' => ['required'],
            'solicitante_id' => $this->regraIdNaEmpresa('users', required: false, nullable: true),
            'abono_pecuniario' => ['required', 'boolean'],
            'adiantamento_decimo_terceiro' => ['required', 'boolean'],
            'gestor_id' => $this->regraIdNaEmpresa('users', required: false, nullable: true),
            'admissao_id' => ['sometimes', 'nullable', 'integer'],
            'centro_custo_id' => $this->regraIdNaEmpresa('centro_custos', required: false, nullable: true),
            'obs_solicitante' => ['sometimes', 'nullable'],
            'anexos' => ['sometimes', 'nullable', 'array'],
            'anexosDel' => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'periodo_aquisitivo_id.required' => 'Informe o período aquisitivo.',
            'data_saida.required' => 'Informe a data de saída.',
            'data_retorno.required' => 'Informe a data de retorno.',
            'ultima_data.required' => 'Informe a data de início.',
            'qnt_dias.required' => 'Informe a quantidade de dias.',
            'dias_saldo.required' => 'Informe o saldo de dias.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Editar Férias',
            'erros' => $validator->errors(),
        ], 400));
    }
}
