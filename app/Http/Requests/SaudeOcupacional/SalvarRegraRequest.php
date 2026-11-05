<?php

namespace App\Http\Requests\SaudeOcupacional;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Formato dos dados da regra. Regras de negócio (condições, cargos da empresa, trava NR-7)
 * ficam no domínio (RegrasEmpresaService::normalizar).
 */
class SalvarRegraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // rota já exige módulo + saude_ocupacional_configuracao
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $salvando = !$this->is('*/previa');

        return [
            'nome' => ['required', 'string', 'max:120'],
            'modelo' => ['nullable', 'string', 'max:40'],
            'exame_tipo_ids' => ['required', 'array', 'min:1', 'max:50'],
            'exame_tipo_ids.*' => ['integer', 'min:1'],
            'condicoes' => ['required', 'array', 'min:1', 'max:5'],
            'condicoes.*.tipo' => ['required', 'string', 'max:40'],
            'condicoes.*.valor' => ['present'],
            'unidade' => ['required', 'in:anos,dias'],
            'valor' => ['required', 'integer', 'min:1', 'max:730'],
            'modo_aplicacao' => [$salvando ? 'required' : 'nullable', 'in:futuro,retroativo,a_partir_de'],
            'data_aplicacao' => ['nullable', 'string', 'max:10'],
            'regra_id' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'Dê um nome à regra.',
            'exame_tipo_ids.required' => 'Escolha ao menos um tipo de exame.',
            'condicoes.required' => 'Informe ao menos uma condição.',
            'valor.max' => 'Pela NR-7, a validade do ASO não pode passar de 2 anos (730 dias).',
            'modo_aplicacao.required' => 'Escolha como aplicar a regra.',
        ];
    }
}
