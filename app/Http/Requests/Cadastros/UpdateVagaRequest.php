<?php

namespace App\Http\Requests\Cadastros;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateVagaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cadastro_vagas_update');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->input('ativo') == 'true',
            'cbo_id' => empty($this->input('cbo_id')) ? null : (int) $this->input('cbo_id'),
        ]);
    }

    public function rules(): array
    {
        $vaga = $this->route('vaga');
        // L13 / binding: parâmetro de rota pode vir como model ou id escalar.
        $vagaId = is_object($vaga) ? ($vaga->id ?? null) : $vaga;

        return [
            'nome' => [
                'required',
                Rule::unique('vagas')->ignore($vagaId)->where(function ($query) {
                    return $this->aplicarSoftDeleteSeExistir(
                        $query->whereNome($this->input('nome'))->whereEmpresaId(auth()->user()->empresa_id),
                        'vagas'
                    );
                }),
            ],
            'ativo' => 'required|boolean',
            'cbo_id' => ['nullable', 'integer', $this->existsAtivo('cbos')],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao atualizar Vaga',
            'erros' => $validator->errors(),
        ], 400));
    }
}
