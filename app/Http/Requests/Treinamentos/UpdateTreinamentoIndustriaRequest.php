<?php

namespace App\Http\Requests\Treinamentos;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateTreinamentoIndustriaRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();

        $input['vinculo_todos_cargos'] = $this->normalizeBoolean($input['vinculo_todos_cargos'] ?? false);
        $input['cargo_ids'] = $input['vinculo_todos_cargos']
            ? []
            : $this->normalizeIds($input['cargo_ids'] ?? []);
        $input['padrao_treinamento_ids'] = $this->normalizeIds($input['padrao_treinamento_ids'] ?? []);

        $this->replace($input);
    }

    public function rules(): array
    {
        $vinculoTodosCargos = (bool) $this->boolean('vinculo_todos_cargos');

        return [
            'label' => ['required'],
            'label_reduzida' => [Rule::requiredIf((bool) $this->input('exibir_na_carteira'))],
            'descricao' => ['required'],
            'ativo' => ['required'],
            'vinculo_todos_cargos' => ['required', 'boolean'],
            'cargo_ids' => ['sometimes', 'nullable', 'array'],
            'cargo_ids.*' => $vinculoTodosCargos
                ? ['integer']
                : ['integer', $this->existsNaEmpresa('vagas')->where(fn ($query) => $query->whereAtivo(true))],
            'padrao_treinamento_ids' => ['sometimes', 'nullable', 'array'],
            'padrao_treinamento_ids.*' => ['integer', $this->existsNaEmpresa('padroes_treinamento')],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao Editar Treinamento Indústria',
            'erros' => $validator->errors(),
        ], 400));
    }

    private function normalizeIds($ids): array
    {
        if (!is_array($ids)) {
            return [];
        }

        return collect($ids)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return $value === 1 || $value === '1' || $value === 'true' || $value === true;
    }
}
