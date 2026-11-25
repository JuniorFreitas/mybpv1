<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEmpresaConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Controller original não possuía authorize() específico;
        // autorização garantida pelo middleware de rota `can:controle_ponto_config_empresa`.
        // EmpresaConfig usa TenantTrait (ScopeEmpresa), então o model binding de {config}
        // já restringe o registro à empresa autenticada.
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'tipo_frequencia' => 'required|min:1',
            'limite_tolerancia' => 'required|numeric|min:1',
            'tempo_limite_falta' => 'required|numeric|min:1',
            'tempo_limite_saida' => 'required|numeric|min:1',
            'dia_nova_frequencia' => 'required|numeric|min:1',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao salvar as configuraçôes da empresa',
            'erros' => $validator->errors(),
        ], 400));
    }
}
