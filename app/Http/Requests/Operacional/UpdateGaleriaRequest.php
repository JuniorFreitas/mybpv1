<?php

namespace App\Http\Requests\Operacional;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Models\Galeria;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateGaleriaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->input('ativo') == 'true',
        ]);
    }

    public function rules(): array
    {
        /** @var Galeria $galeria */
        $galeria = $this->route('galeria');

        return [
            'titulo' => [
                'required',
                'min:1',
                Rule::unique('galerias', 'titulo')->ignore($galeria->id)->where(function ($query) {
                    return $this->aplicarSoftDeleteSeExistir($query, 'galerias');
                }),
            ],
            'ativo' => ['required'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao atualizar Galeria',
            'erros' => $validator->errors(),
        ], 400));
    }
}
