<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreFilialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('administracao_clientes_insert');
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
