<?php

namespace App\Http\Requests\Cadastros;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AssociarPerimetroRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'perimetro_id' => array_filter([
                Rule::requiredIf(empty($this->input('perimetrosSelecionados'))),
                'nullable',
                'integer',
                $this->input('perimetro_id') !== null && $this->input('perimetro_id') !== ''
                    ? $this->existsNaEmpresa('empresa_perimetros')
                    : null,
            ]),
            'perimetrosSelecionados' => [
                Rule::requiredIf(is_null($this->input('perimetro_id'))),
                'nullable',
                'array',
            ],
            'perimetrosSelecionados.*' => $this->regraIdNaEmpresa('empresa_perimetros', required: false),
            'funcionariosSelecionados' => 'required|array|min:1',
            'funcionariosSelecionados.*' => $this->regraIdNaEmpresa('users', required: false),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao associar perímetros',
            'erros' => $validator->errors(),
        ], 400));
    }
}
