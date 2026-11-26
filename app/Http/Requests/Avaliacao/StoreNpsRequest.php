<?php

namespace App\Http\Requests\Avaliacao;

use App\Models\NpsPergunta;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreNpsRequest extends FormRequest
{
    /** @var list<int> */
    private array $idsAtivos = [];

    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $empresaId = auth()->user()->empresa_id;
        $this->idsAtivos = NpsPergunta::ativasParaEmpresa($empresaId)->pluck('id')->all();
    }

    public function rules(): array
    {
        return [
            'respostas' => ['required', 'array', 'min:1'],
            'respostas.*.nps_pergunta_id' => ['required', 'integer', 'in:' . implode(',', $this->idsAtivos ?: [0])],
            'respostas.*.nota' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->idsAtivos === []) {
                throw new HttpResponseException(response()->json([
                    'erro' => true,
                    'mensagem' => 'Nenhuma pergunta ativa para responder.',
                ], 422));
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'erro' => true,
            'mensagem' => 'Dados inválidos.',
            'erros' => $validator->errors(),
        ], 422));
    }
}
