<?php

namespace App\Http\Requests\Documentos;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCarteiraAssinaturaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cadastro_carteira_assinatura');
    }

    public function rules(): array
    {
        return [
            'nome' => 'required',
            'tipo' => 'required',
            'padrao_treinamento_id' => $this->regraIdNaEmpresa('padroes_treinamento', required: false, nullable: true),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Cadastrar Assinatura Carteira',
            'erros' => $validator->errors(),
        ], 400));
    }
}
