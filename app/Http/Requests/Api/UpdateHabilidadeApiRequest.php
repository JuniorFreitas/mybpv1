<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateHabilidadeApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->tokenCan('habilidade_update');
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Acesso negado',
            'error' => true,
        ], 403));
    }

    public function rules(): array
    {
        $habilidade = $this->route('habilidade');
        $habilidadeId = is_object($habilidade) ? $habilidade->id : $habilidade;

        return [
            'nome' => "required|unique:habilidades,nome,{$habilidadeId}|max:255",
            'descricao' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json($validator->messages(), 400));
    }
}
