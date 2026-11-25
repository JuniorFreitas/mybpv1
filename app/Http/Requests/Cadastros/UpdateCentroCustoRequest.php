<?php

namespace App\Http\Requests\Cadastros;

use App\Http\Requests\Concerns\ValidatesTenantOwned;
use App\Models\ClienteFilial;
use App\Services\CentroCusto\CentroCustoUnicidadeService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCentroCustoRequest extends FormRequest
{
    use ValidatesTenantOwned;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('cadastro_centrocusto_update');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->input('ativo') == 'true',
        ]);
    }

    public function rules(CentroCustoUnicidadeService $unicidadeService): array
    {
        $dados = $this->all();
        $ignoreId = (int) $this->route('centrocusto');

        $rules = [
            'label' => ['required', $unicidadeService->regraLabel($dados, $ignoreId)],
            'gestor_id' => $this->regraIdNaEmpresa('users', required: false, nullable: true),
            'gestor_substituto_id' => $this->regraIdNaEmpresa('users', required: false, nullable: true),
        ];

        if ((new ClienteFilial())->temFilial()) {
            $rules['campoCnpj'] = ['required', 'string'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'label.unique' => 'Já existe um Centro de Custo ativo com este Nome no mesmo CNPJ.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'msg' => 'Erro ao atualizar Centro de Custos',
            'erros' => $validator->errors(),
        ], 400));
    }
}
