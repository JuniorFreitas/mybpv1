<?php

namespace App\Http\Requests\Movimentacao;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class StoreTransferenciaPrevistaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $origem = $this->input('centro_custo_origem_id');
        $this->merge([
            'centro_custo_origem_id' => ($origem === null || $origem === '') ? null : (int) $origem,
        ]);
    }

    public function rules(): array
    {
        return [
            'centro_custo_origem_id' => $this->regraIdNaEmpresa('centro_custos'),
            'centro_custo_destino_id' => $this->regraIdNaEmpresa('centro_custos'),
            'colaborador_id' => $this->regraIdNaEmpresa('users'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::warning('transferencia.validacao_falhou', [
            'empresa_id' => auth()->user()->empresa_id ?? null,
            'user_id' => auth()->id(),
            'erros' => $validator->errors()->toArray(),
            'centro_custo_origem_id' => $this->input('centro_custo_origem_id'),
            'centro_custo_destino_id' => $this->input('centro_custo_destino_id'),
            'colaborador_id' => $this->input('colaborador_id'),
        ]);

        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Solicitar Transferência',
            'erros' => $validator->errors(),
        ], 400));
    }
}
