<?php

namespace App\Http\Requests\Fornecedor;

use App\Models\Fornecedor;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateFornecedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('administracao_fornecedores_update');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->input('ativo') == 'true' || $this->input('ativo') === true,
        ]);
    }

    public function rules(): array
    {
        $fornecedor = $this->route('fornecedor');
        $fornecedorId = $fornecedor instanceof Fornecedor ? $fornecedor->id : $fornecedor;

        $rules = [
            'tipo_pessoa' => 'required',
            'contato' => 'required',
            'uf' => 'required|min:2',
            'logradouro' => 'required|min:3',
            'bairro' => 'required|min:3',
            'municipio' => 'required|min:3',
            'email' => 'required|email',
            'ativo' => 'required',
        ];

        if ($this->input('tipo_pessoa') == Fornecedor::PESSOA_JURIDICA) {
            $rules['cnpj'] = 'required|min:18|unique:fornecedores,cnpj,' . $fornecedorId;
            $rules['razao_social'] = 'required|min:2';
        } else {
            $rules['cpf'] = 'required|min:14|unique:fornecedores,cpf,' . $fornecedorId;
            $rules['nome'] = 'required|min:2';
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao atualizar o Fornecedor',
            'erros' => $validator->errors(),
        ], 400));
    }
}
