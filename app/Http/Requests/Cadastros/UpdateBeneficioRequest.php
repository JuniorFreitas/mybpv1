<?php

namespace App\Http\Requests\Cadastros;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBeneficioRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cadastro_beneficio_update');
    }

    public function rules(): array
    {
        return [
            'nome' => 'required',
            'tipobeneficio_id' => $this->regraIdNaEmpresa('tipo_beneficios'),
            'valor' => 'required',
            'aplicacao' => 'required',
            'periodicidade' => 'required',
            'valor_descontado' => 'required',
            'opcao_desconto' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Editar Benefício',
            'erros' => $validator->errors(),
        ], 400));
    }
}
