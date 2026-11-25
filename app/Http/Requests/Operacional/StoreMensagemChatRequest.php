<?php

namespace App\Http\Requests\Operacional;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMensagemChatRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // Garante que o destinatário pertence à mesma empresa do remetente autenticado.
            'para_id' => $this->regraIdNaEmpresa('users', required: false, nullable: true),
            'grupo_id' => ['nullable', 'integer', 'min:1'],
            'mensagem' => ['required', 'min:1'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao enviar a mensagem',
            'erros' => $validator->errors(),
        ], 400));
    }
}
