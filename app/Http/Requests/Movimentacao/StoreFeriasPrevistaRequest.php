<?php

namespace App\Http\Requests\Movimentacao;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreFeriasPrevistaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();

        if (array_key_exists('tem_faltas', $input)) {
            $input['tem_faltas'] = filter_var($input['tem_faltas'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                ?? ($input['tem_faltas'] === 'true' || $input['tem_faltas'] === true);
        }

        if (array_key_exists('qnt_faltas', $input) && ($input['qnt_faltas'] === null || $input['qnt_faltas'] === '')) {
            $input['qnt_faltas'] = 0;
        }

        $this->replace($input);
    }

    public function rules(): array
    {
        return [
            'qnt_dias' => ['required'],
            'dias_saldo' => ['required'],
            // colaborador_id no fluxo de férias é feedback_curriculos.id (não users.id).
            // Ver Colaborador.vue (tipo=ferias) e AutoCompletesController::colaboradoresFerias.
            'periodo_aquisitivo_id' => $this->regraIdAtivo('periodos_aquisitivos'),
            'colaborador_id' => $this->regraIdNaEmpresa('feedback_curriculos'),
            'gestor_id' => $this->regraIdNaEmpresa('users'),
            'data_admissao' => ['required'],
            'ultima_data' => ['required'],
            'admissao_id' => ['sometimes', 'nullable', 'integer'],
            'centro_custo_id' => $this->regraIdNaEmpresa('centro_custos', required: false, nullable: true),
            'tem_faltas' => ['sometimes', 'boolean'],
            'qnt_faltas' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'colaborador_id.required' => 'Informe um colaborador para continuar',
            'gestor_id.required' => 'Informe um gestor para aprovação',
            'data_admissao.required' => 'Atualize a data de admissão do colaborador',
            'qnt_dias.required' => 'Informe a quantidade de dias',
            'dias_saldo.required' => 'Informe o saldo de dias',
            'periodo_aquisitivo_id.required' => 'Informe o período aquisitivo',
            'ultima_data.required' => 'Informe a data de início das férias',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Solicitar Férias',
            'erros' => $validator->errors(),
        ], 400));
    }
}
