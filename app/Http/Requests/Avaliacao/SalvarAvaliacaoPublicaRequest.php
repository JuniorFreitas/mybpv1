<?php

namespace App\Http\Requests\Avaliacao;

use App\Services\AvaliacaoNoventaService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Avaliação pública via token: authorize só exige usuário autenticado.
 * A autorização de gestor/RH permanece no controller (token + centro de custo).
 * O merge de gestor_imediato ocorre aqui para validar após o preenchimento automático.
 */
class SalvarAvaliacaoPublicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $token = $this->route('token');
        if (!$token || !auth()->check()) {
            return;
        }

        $validacao = app(AvaliacaoNoventaService::class)->validarTokenAvaliacao($token);
        if (!($validacao['valid'] ?? false)) {
            return;
        }

        $vencimento = $validacao['vencimento'];
        $user = auth()->user();
        $gestorIdCentroCusto = optional(optional(optional($vencimento->FeedbackCurriculo)->Admissao)->CentroCusto)->gestor_id;
        $ehGestorCentroCusto = $user && $gestorIdCentroCusto && ($user->id === $gestorIdCentroCusto);

        if ($ehGestorCentroCusto) {
            $this->merge(['gestor_imediato' => $user->nome]);
        }
    }

    public function rules(): array
    {
        return [
            'gestor_imediato' => 'required|string|max:255',
            'observacao' => 'nullable|string|max:5000',
            'definicao_contrato' => 'required|in:prorroga,finaliza',
            'perguntas' => 'required|array',
            'perguntas.*.id' => 'required|integer',
            'perguntas.*.nota' => 'required|integer|min:1|max:5',
        ];
    }

    public function messages(): array
    {
        return [
            'gestor_imediato.required' => 'O campo Gestor Imediato é obrigatório',
            'definicao_contrato.required' => 'Selecione a definição sobre o colaborador (Prorroga ou Finaliza o contrato).',
            'definicao_contrato.in' => 'Definição inválida.',
            'perguntas.required' => 'Todas as perguntas devem ser respondidas',
            'perguntas.*.nota.required' => 'Todas as perguntas devem ter uma nota',
            'perguntas.*.nota.min' => 'A nota mínima é 1',
            'perguntas.*.nota.max' => 'A nota máxima é 5',
        ];
    }
}
