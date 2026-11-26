<?php

namespace App\Http\Requests\Documentos;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Models\DocumentoContratos;
use App\Models\Sistema;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDocumentoContratoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();

        if (isset($input['dados_cadastrais']['email'])) {
            $input['dados_cadastrais']['email'] = trim(mb_strtolower($input['dados_cadastrais']['email']));
        }
        $input['ativo'] = ($input['ativo'] ?? null) == 'true';

        $this->replace($input);
    }

    public function rules(): array
    {
        $dados = $this->all();

        return [
            'dados_cadastrais.nome' => [function ($attribute, $value, $fail) use ($dados) {
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Física' && strlen($value) <= 2) {
                    $mensagem = strlen($value) <= 2 ? 'O campo nome deve conter no minimo três caracteres ' : 'Preencha o campo informando nome. ';
                    $fail($mensagem);
                }
            }],
            'dados_cadastrais.cpf' => [function ($attribute, $value, $fail) use ($dados) {
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Física' && !Sistema::validaRuleCPF($value)) {
                    $fail('Informe um CPF válido.');
                }
                $verificaCpf = DocumentoContratos::whereJsonContains('dados_cadastrais->cpf', $value)->first();
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Física' && $verificaCpf) {
                    $fail('CPF já cadastrado');
                }
            }],
            'dados_cadastrais.razao_social' => [function ($attribute, $value, $fail) use ($dados) {
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Jurídica' && strlen($value) <= 3) {
                    $fail('Preencha o campo informando razão social.');
                }
            }],
            'dados_cadastrais.nome_fantasia' => [function ($attribute, $value, $fail) use ($dados) {
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Jurídica' && strlen($value) <= 3) {
                    $fail('Preencha o campo informando nome fantasia.');
                }
            }],
            'dados_cadastrais.cnpj' => [function ($attribute, $value, $fail) use ($dados) {
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Jurídica' && strlen($value) <= 14) {
                    $fail('Preencha o campo informando CNPJ.');
                }
                $verificaCnpj = DocumentoContratos::whereJsonContains('dados_cadastrais->cnpj', $value)->first();
                if ($dados['dados_cadastrais']['tipo'] == 'Pessoa Jurídica' && $verificaCnpj) {
                    $fail('CNPJ já cadastrado');
                }
            }],
            'dados_cadastrais.email' => ['required', 'email:rfc,dns'],
            'dados_cadastrais.telefones' => ['required', 'array', 'min:1'],
            'dados_cadastrais.telefones.*.numero' => ['required', 'min:14'],
            'dados_cadastrais.cep' => ['required', 'min:9'],
            'dados_cadastrais.logradouro' => ['required'],
            'dados_cadastrais.bairro' => ['required'],
            'dados_cadastrais.municipio' => ['required'],
            'dados_cadastrais.uf' => ['required', 'min:2'],
            'dados_cadastrais.ramo' => ['required'],
            'dados_cadastrais.area_id' => ['required'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar Documento Contrato',
            'erros' => $validator->errors(),
        ], 400));
    }
}
