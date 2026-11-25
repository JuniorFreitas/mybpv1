<?php

namespace App\Http\Requests\Cadastros;

use App\Models\Cliente;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->input('ativo') == 'true' || $this->input('ativo') === true,
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'tipo' => 'required',
            'area_id' => 'required',
            'contato' => 'required',
            'aniversario' => 'required',
            'uf' => 'required|min:2',
            'logradouro' => 'required|min:3',
            'bairro' => 'required|min:3',
            'municipio' => 'required|min:3',
            'email' => 'required|email',
            'ativo' => 'required',
            'telefones' => 'required|array|min:1',
        ];

        if ($this->input('tipo') == Cliente::TIPO_PESSOA_JURIDICA) {
            $rules['cnpj'] = 'required|min:18|unique:clientes,cnpj';
            $rules['razao_social'] = 'required|min:2';
        } else {
            $rules['cpf'] = 'required|min:14|unique:clientes,cpf';
            $rules['nome'] = 'required|min:2';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'telefones.required' => 'É Necessário Informar pelo menos Um número de telefone',
            'telefones.min' => 'É Necessário Informar pelo menos Um número de telefone',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar Cliente',
            'erros' => $validator->errors(),
        ], 400));
    }
}
