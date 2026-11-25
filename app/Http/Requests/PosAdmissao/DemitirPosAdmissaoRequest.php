<?php

namespace App\Http\Requests\PosAdmissao;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DemitirPosAdmissaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'demissao.cipa' => ['required'],
            'demissao.motivo_rescisao_id' => ['required', 'integer', 'min:1'],
            'demissao.tipo_aviso_id' => ['required', 'integer', 'min:1'],
            'demissao.solicitado_por' => ['required', 'string', 'max:255'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao demitir',
            'erros' => $validator->errors(),
        ], 400));
    }
}
