<?php

namespace App\Http\Requests\Operacional;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAtaReuniaoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'local' => ['required'],
            'assuntos' => ['required'],
            'tipos' => ['required'],
            'acoes' => ['required'],
            'participantes' => ['required'],
            'centro_custo_id' => $this->regraIdNaEmpresa('centro_custos', required: false, nullable: true),
            'area_etiqueta_id' => $this->regraIdNaEmpresa('area_etiquetas', required: false, nullable: true),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Editar Ata Reunião',
            'erros' => $validator->errors(),
        ], 400));
    }
}
