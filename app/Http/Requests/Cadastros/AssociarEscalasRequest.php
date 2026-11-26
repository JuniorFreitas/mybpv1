<?php

namespace App\Http\Requests\Cadastros;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AssociarEscalasRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'escala_id' => array_filter([
                Rule::requiredIf(empty($this->input('escalasSelecionadas'))),
                'nullable',
                'integer',
                $this->input('escala_id') !== null && $this->input('escala_id') !== ''
                    ? $this->existsNaEmpresa('empresa_escalas')
                    : null,
            ]),
            'escalasSelecionadas' => [
                Rule::requiredIf(is_null($this->input('escala_id'))),
                'nullable',
                'array',
            ],
            'escalasSelecionadas.*' => $this->regraIdNaEmpresa('empresa_escalas', required: false),
            'funcionariosSelecionados' => 'required|array|min:1',
            'funcionariosSelecionados.*' => $this->regraIdNaEmpresa('users', required: false),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao associar escalas',
            'erros' => $validator->errors(),
        ], 400));
    }
}
