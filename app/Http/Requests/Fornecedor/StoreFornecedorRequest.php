<?php

namespace App\Http\Requests\Fornecedor;

use App\Models\Fornecedor;
use Illuminate\Foundation\Http\FormRequest;

class StoreFornecedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('administracao_fornecedores_insert');
    }

    public function rules(): array
    {
        $rules = [
            'tipo_pessoa' => 'required|in:' . Fornecedor::PESSOA_FISICA . ',' . Fornecedor::PESSOA_JURIDICA,
            'contato' => 'required|string|min:2',
            'uf' => 'required|string|size:2',
            'logradouro' => 'required|string|min:3',
            'bairro' => 'required|string|min:3',
            'municipio' => 'required|string|min:3',
            'email' => 'required|email|unique:users,login',
            'ativo' => 'boolean',
            'telefones' => 'required|array|min:1',
            'telefones.*.numero' => 'required|string',
            'anexos' => 'sometimes|array',
            'servicos' => 'sometimes|array',
            'servicos.*.nome' => 'required_with:servicos|string',
            'servicos.*.ativo' => 'sometimes|boolean',
        ];

        if ($this->input('tipo_pessoa') == Fornecedor::PESSOA_JURIDICA) {
            $rules = array_merge($rules, [
                'cnpj' => 'required|string|min:18|unique:fornecedores,cnpj',
                'razao_social' => 'required|string|min:2',
                'nome_fantasia' => 'sometimes|string|min:2',
            ]);
        } else {
            $rules = array_merge($rules, [
                'cpf' => 'required|string|min:14|unique:fornecedores,cpf',
                'nome' => 'required|string|min:2',
            ]);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'telefones.required' => 'É necessário informar pelo menos um número de telefone',
            'telefones.min' => 'É necessário informar pelo menos um número de telefone',
            'cnpj.unique' => 'Este CNPJ já está cadastrado',
            'cpf.unique' => 'Este CPF já está cadastrado',
            'email.unique' => 'Este email já está cadastrado',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => filter_var($this->ativo ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);

        // Converter ativo nos serviços
        if ($this->has('servicos')) {
            $servicos = $this->servicos;
            foreach ($servicos as &$servico) {
                $servico['ativo'] = filter_var($servico['ativo'] ?? true, FILTER_VALIDATE_BOOLEAN);
            }
            $this->merge(['servicos' => $servicos]);
        }
    }
}
