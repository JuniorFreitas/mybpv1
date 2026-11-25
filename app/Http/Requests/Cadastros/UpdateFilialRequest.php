<?php

namespace App\Http\Requests\Cadastros;

use App\Models\ClienteFilial;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateFilialRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!$this->user()?->can('administracao_clientes_insert')) {
            return false;
        }

        // ClienteFilial não usa TenantTrait (sem global scope de empresa):
        // bloqueia explicitamente edição de filial de outra empresa (IDOR).
        $filial = $this->route('filial');

        if (!$filial instanceof ClienteFilial) {
            return true;
        }

        return (int) $filial->empresa_id === (int) ($this->user()->empresa_id ?? 0);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->input('ativo') == 'true',
        ]);
    }

    public function rules(): array
    {
        return [
            'dados.cnpj' => 'required|min:18',
            'dados.razao_social' => 'required|min:2',
            'dados.nome_fantasia' => 'required|min:2',
            'dados.area_id' => 'required',
            'dados.ramo' => 'required',
            'dados.cep' => 'required|min:9',
            'dados.uf' => 'required|min:2',
            'dados.logradouro' => 'required|min:3',
            'dados.bairro' => 'required|min:3',
            'dados.municipio' => 'required|min:3',
            'dados.email' => 'required|email',
            'dados.telefone' => 'required|min:14',
            'ativo' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar Filial',
            'erros' => $validator->errors(),
        ], 400));
    }
}
