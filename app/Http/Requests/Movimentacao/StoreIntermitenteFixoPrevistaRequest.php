<?php

namespace App\Http\Requests\Movimentacao;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreIntermitenteFixoPrevistaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'centro_custo_id' => $this->regraIdNaEmpresa('centro_custos'),
            'centro_custo_filial_id' => $this->regraIdNaEmpresa('centro_custo_filials', required: false, nullable: true),
            'colaborador_id' => $this->regraIdNaEmpresa('users'),
            'cargo_anterior_id' => $this->regraIdNaEmpresa('vagas'),
            'salario_anterior_format' => ['required'],
            'novo_cargo_id' => $this->regraIdNaEmpresa('vagas'),
            'novo_salario_format' => ['required'],
            'anterior_vaga_aberta_id' => $this->regraIdNaEmpresa('vagas_abertas'),
            'nova_vaga_aberta_id' => $this->regraIdNaEmpresa('vagas_abertas'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Solicitar Mudança Intermitente Fixo',
            'erros' => $validator->errors(),
        ], 400));
    }
}
