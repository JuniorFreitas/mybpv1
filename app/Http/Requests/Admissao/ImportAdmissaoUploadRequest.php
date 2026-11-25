<?php

namespace App\Http\Requests\Admissao;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ImportAdmissaoUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'planilha' => ['required', 'file', 'mimes:xlsx', 'max:20480'],
            'empresa_id' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function passedValidation(): void
    {
        $empresaId = (int) $this->input('empresa_id');
        $user = $this->user();

        if ($user && $user->empresa_id !== null && (int) $user->empresa_id !== $empresaId) {
            throw ValidationException::withMessages([
                'empresa_id' => ['Empresa não autorizada.'],
            ]);
        }
    }
}
