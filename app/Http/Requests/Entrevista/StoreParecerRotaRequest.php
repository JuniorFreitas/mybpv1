<?php

namespace App\Http\Requests\Entrevista;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreParecerRotaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'feedback_id' => $this->regraIdNaEmpresa('feedback_curriculos'),
            'tem_rota' => ['required', 'boolean'],
            'pega_onibus' => ['nullable', 'boolean'],
            'vale_transporte' => ['required', 'boolean'],
            'rota_atende' => ['required', 'boolean'],
            'rota_tipo' => ['required', 'string', 'max:100'],
            'quem_entrevistou' => ['required', 'string', 'min:3', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'feedback_id.required' => 'ID do feedback é obrigatório',
            'feedback_id.exists' => 'Feedback não encontrado',
            'tem_rota.required' => 'Informe se tem rota',
            'tem_rota.boolean' => 'Campo tem rota deve ser verdadeiro ou falso',
            'vale_transporte.required' => 'Informe sobre vale transporte',
            'vale_transporte.boolean' => 'Campo vale transporte deve ser verdadeiro ou falso',
            'rota_atende.required' => 'Informe se a rota atende',
            'rota_atende.boolean' => 'Campo rota atende deve ser verdadeiro ou falso',
            'rota_tipo.required' => 'Tipo de rota é obrigatório',
            'rota_tipo.max' => 'Tipo de rota deve ter no máximo 100 caracteres',
            'quem_entrevistou.required' => 'Nome do entrevistador é obrigatório',
            'quem_entrevistou.min' => 'Nome do entrevistador deve ter pelo menos 3 caracteres',
            'quem_entrevistou.max' => 'Nome do entrevistador deve ter no máximo 255 caracteres',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao salvar a entrevista',
            'erros' => $validator->errors(),
        ], 400));
    }
}
