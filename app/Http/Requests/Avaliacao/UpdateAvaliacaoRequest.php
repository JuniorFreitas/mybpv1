<?php

namespace App\Http\Requests\Avaliacao;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Rules\TenantUniqueRules;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use MasterTag\DataHora;

class UpdateAvaliacaoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mostrar_notas_avaliador_final' => (bool) ($this->input('mostrar_notas_avaliador_final') ?? false),
        ]);
    }

    public function rules(): array
    {
        return [
            'titulo' => [
                function ($attribute, $value, $fail) {
                    if (strlen((string) $value) <= 3) {
                        $fail('Informe uma título maior que 3 caracteres.');
                    }
                },
                'required',
                new TenantUniqueRules('avaliacoes', $this->segment(5)),
            ],
            'avaliacao_tipo_id' => $this->regraIdNaEmpresa('avaliacoes_tipos'),
            'data_inicio_prazo' => [function ($attribute, $value, $fail) {
                $diffDias = DataHora::diferencaDias($value, $this->input('data_fim_prazo'));

                if ($diffDias < 0) {
                    $fail('Data Fim precisa ser maior que a Data início');
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
            'avaliacao_tipo_id.exists' => 'Verificar o tipo de avaliação',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao editar a avaliação',
            'erros' => $validator->errors(),
        ], 400));
    }
}
