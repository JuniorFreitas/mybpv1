<?php

namespace App\Http\Requests\Fornecedor;

use App\Models\Fornecedor;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreFornecedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('administracao_fornecedores_insert');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => filter_var($this->ativo ?? true, FILTER_VALIDATE_BOOLEAN),
            'cadastrou' => auth()->id(),
            'empresa_id' => auth()->user()->empresa_id ?? null,
        ]);

        if ($this->has('servicos')) {
            $servicos = $this->servicos;
            foreach ($servicos as &$servico) {
                $servico['ativo'] = filter_var($servico['ativo'] ?? true, FILTER_VALIDATE_BOOLEAN);
            }
            $this->merge(['servicos' => $servicos]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'tipo_pessoa' => 'required|in:' . Fornecedor::PESSOA_FISICA . ',' . Fornecedor::PESSOA_JURIDICA,
            'contato' => 'required',
            'uf' => 'required|min:2',
            'logradouro' => 'required|min:3',
            'bairro' => 'required|min:3',
            'municipio' => 'required|min:3',
            'email' => 'required|email|unique:users,login',
            'ativo' => 'required',
            'telefones' => 'required|array|min:1',
            'anexos' => 'sometimes|array',
            'servicos' => 'sometimes|array',
            'servicos.*.nome' => 'required_with:servicos|string',
            'servicos.*.ativo' => 'sometimes|boolean',
        ];

        if ($this->input('tipo_pessoa') == Fornecedor::PESSOA_JURIDICA) {
            $rules['cnpj'] = 'required|min:18|unique:fornecedores,cnpj';
            $rules['razao_social'] = 'required|min:2';
        } else {
            $rules['cpf'] = 'required|min:14|unique:fornecedores,cpf';
            $rules['nome'] = 'required|min:2';
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

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar Fornecedor',
            'erros' => $validator->errors(),
        ], 400));
    }
}
