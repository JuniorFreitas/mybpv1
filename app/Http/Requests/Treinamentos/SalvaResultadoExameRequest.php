<?php

namespace App\Http\Requests\Treinamentos;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SalvaResultadoExameRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        if (!$this->boolean('exame_realizado')) {
            return [];
        }

        return [
            'data_realizacao' => ['required_if:exame_realizado,1', 'date_format:d/m/Y'],
            'resultado.result' => ['required_if:exame_realizado,1', 'in:Apto,Apto com Restrição,Inapto'],
            'resultado.pendencias' => ['required_if:exame_realizado,1', 'in:Sim,Não'],
            'resultado.pendencias_quais' => ['required_if:resultado.pendencias,Sim'],
            'resultado.aprovado' => ['required_if:exame_realizado,1'],
            'resultado.trabalho_altura' => ['required_if:exame_realizado,1', 'in:Sim,Não,Não se aplica'],
            'resultado.observacao' => ['max:500'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao atualizar o resultado',
            'erros' => $validator->errors(),
        ], 400));
    }
}
