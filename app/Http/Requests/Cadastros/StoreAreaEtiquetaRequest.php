<?php

namespace App\Http\Requests\Cadastros;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreAreaEtiquetaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cadastro_areaetiqueta');
    }

    public function rules(): array
    {
        $regra = Rule::unique('area_etiquetas')->where(function ($query) {
            return $this->aplicarSoftDeleteSeExistir(
                $query->whereEmpresaId(auth()->user()->empresa_id)
                    ->whereLabel($this->input('label')),
                'area_etiquetas'
            );
        });

        return [
            'label' => ['required', $regra],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao cadastrar Área',
            'erros' => $validator->errors(),
        ], 400));
    }
}
