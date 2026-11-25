<?php

namespace App\Http\Requests\Intermitente;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreIntermitenteRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $tipoId = (int) $this->input('tipo_id', 0);
        $areaId = (int) $this->input('area_id', 0);

        $rules = [
            'tipo_id' => ['required', 'integer'],
            'colaboradores' => ['required', 'array', 'min:1'],
            'colaboradores.*.id' => $this->existsNaEmpresa('feedback_curriculos'),
            'centro_custo_id' => $this->regraIdNaEmpresa('centro_custos'),
            'area_id' => ['nullable', 'integer'],
        ];

        if ($tipoId > 0) {
            $rules['tipo_id'][] = $this->existsNaEmpresa('intermitente_tipos');
        }

        if ($areaId > 0) {
            $rules['area_id'][] = $this->existsNaEmpresa('area_etiquetas');
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Salvar Informações',
            'erros' => $validator->errors(),
        ], 400));
    }
}
