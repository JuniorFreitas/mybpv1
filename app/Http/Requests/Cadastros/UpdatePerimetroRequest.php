<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePerimetroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('controle_ponto_perimetros_update');
    }

    public function rules(): array
    {
        $perimetro = $this->route('perimetro');

        return [
            'descricao' => 'required|min:3|unique:empresa_perimetros,descricao,' . $perimetro?->id,
            'lat' => 'numeric',
            'long' => 'numeric',
            'obrigatorio' => 'required|boolean',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao salvar o perímetro',
            'erros' => $validator->errors(),
        ], 400));
    }
}
