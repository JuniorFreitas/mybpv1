<?php

namespace App\Http\Requests\Entrevista;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreParecerEntrevistaTecnicaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'feedback_id' => ['nullable', 'integer', $this->existsNaEmpresa('feedback_curriculos')],
            'opera_plat_movel' => ['required'],
            'tipo_contratacao' => ['required'],
            'opera_plat_ponte' => ['required'],
            'resultado_final' => ['required'],
            'nota' => ['required'],
            'quem_entrevistou' => ['required', 'min:3'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao alterar a entrevista',
            'erros' => $validator->errors(),
        ], 400));
    }
}
