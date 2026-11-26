<?php

namespace App\Http\Requests\Movimentacao;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use MasterTag\DataHora;

class StoreMudancaCargoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'colaborador_id' => $this->regraIdNaEmpresa('users'),
            'gestor_id' => $this->regraIdNaEmpresa('users'),
            'admissao_id' => ['required', 'integer'],
            'novo_centro_custo_id' => [
                'nullable',
                'required_if:mantem_centro_custo,false',
                'integer',
                $this->existsNaEmpresa('centro_custos'),
            ],
            'novo_centro_custo_filial_id' => $this->regraIdNaEmpresa('centro_custo_filials', required: false, nullable: true),
            'anterior_centro_custo_id' => $this->regraIdNaEmpresa('centro_custos', required: false, nullable: true),
            'nova_vaga_aberta_id' => [
                'nullable',
                'required_if:mantem_cargo,false',
                'integer',
                $this->existsNaEmpresa('vagas_abertas'),
            ],
            'anterior_vaga_aberta_id' => $this->regraIdNaEmpresa('vagas_abertas', required: false, nullable: true),
            'nova_funcao' => ['required_if:mantem_funcao,false'],
            'novo_salario' => ['required_if:mantem_salario,false'],
            'treinamento_data_inicio' => ['required_if:treinamento_funcao,true'],
            'treinamento_data_fim' => ['required_if:treinamento_funcao,true'],
            'treinamento_termo_ciencia' => [
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (filter_var($this->input('treinamento_funcao'), FILTER_VALIDATE_BOOLEAN)) {
                        if (empty($value) || !is_array($value) || count($value) === 0) {
                            $fail('O Termo de Ciência de Treinamento é obrigatório quando há treinamento na função');
                        }
                    }
                },
            ],
            'treinamento_certificado' => [
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (filter_var($this->input('treinamento_funcao'), FILTER_VALIDATE_BOOLEAN)) {
                        if (empty($value) || !is_array($value) || count($value) === 0) {
                            $fail('O Certificado de Treinamento é obrigatório quando há treinamento na função');
                        }
                    }
                },
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (!filter_var($this->input('treinamento_funcao'), FILTER_VALIDATE_BOOLEAN)) {
                return;
            }

            $inicio = $this->input('treinamento_data_inicio');
            $fim = $this->input('treinamento_data_fim');
            if (empty($inicio) || empty($fim)) {
                return;
            }

            try {
                $dataInicio = new DataHora($inicio);
                $dataFim = new DataHora($fim);
                if ($dataInicio->dataInsert() > $dataFim->dataInsert()) {
                    $validator->errors()->add('treinamento_data_inicio', 'A data de início do treinamento não pode ser maior que a data de fim do treinamento');
                    $validator->errors()->add('treinamento_data_fim', 'A data de fim do treinamento não pode ser menor que a data de início do treinamento');
                }
            } catch (\Exception) {
                // Conversão inválida: as regras required_if já cobrem ausência.
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Solicitar Mudança de Cargo',
            'erros' => $validator->errors(),
        ], 400));
    }
}
