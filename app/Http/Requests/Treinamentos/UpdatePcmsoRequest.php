<?php

namespace App\Http\Requests\Treinamentos;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePcmsoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();
        $input['ativo'] = $input['ativo'] == 'true';

        $this->replace($input);
    }

    public function rules(): array
    {
        $pcmsoId = (int) ($this->route('pcmso')?->id ?? $this->route('pcmso'));

        $regra = Rule::unique('pcmsos')->where(
            fn ($query) => $this->aplicarSoftDeleteSeExistir(
                $query->whereEmpresaId($this->empresaIdAutenticada())->whereLabel($this->input('label')),
                'pcmsos'
            )
        )->ignore($pcmsoId);

        return [
            'label' => ['required', $regra],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao atualizar PCMSO',
            'erros' => $validator->errors(),
        ], 400));
    }
}
