<?php

namespace App\Http\Requests\Cih;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreTipoCihRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $regra = Rule::unique('cih_tags')->where(function ($query) {
            return $this->aplicarSoftDeleteSeExistir(
                $query->where('empresa_id', $this->empresaIdAutenticada())
                    ->where('label', $this->input('label')),
                'cih_tags'
            );
        });

        return [
            'label' => ['required', $regra],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao criar novo tipo cih',
            'erros' => $validator->errors(),
        ], 400));
    }
}
