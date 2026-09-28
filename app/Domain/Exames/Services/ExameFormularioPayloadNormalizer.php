<?php

namespace App\Domain\Exames\Services;

use App\Models\Formulario;
use Illuminate\Support\Collection;

/**
 * Normaliza o grafo Formulario/Setores/Alternativas para o formato
 * esperado pelo FormularioDefault.vue (chaves em minúsculo).
 */
class ExameFormularioPayloadNormalizer
{
    public function toFrontend(?Formulario $formulario): ?array
    {
        if (!$formulario) {
            return null;
        }

        $setores = $formulario->relationLoaded('Setores')
            ? $formulario->Setores
            : $formulario->Setores()->with('Alternativas.Opcoes')->get();

        return [
            'id' => $formulario->id,
            'titulo' => $formulario->titulo,
            'descricao' => $formulario->descricao,
            'empresa_id' => $formulario->empresa_id,
            'setores' => $this->mapSetores($setores),
        ];
    }

    private function mapSetores(Collection $setores): array
    {
        return $setores->map(function ($setor) {
            $alternativas = $setor->relationLoaded('Alternativas')
                ? $setor->Alternativas
                : $setor->Alternativas()->with('Opcoes')->get();

            return [
                'id' => $setor->id,
                'nome' => $setor->nome,
                'empresa_id' => $setor->empresa_id ?? null,
                'alternativas' => $alternativas->map(function ($alt) {
                    $opcoes = $alt->relationLoaded('Opcoes') ? $alt->Opcoes : $alt->Opcoes()->get();

                    return [
                        'id' => $alt->id,
                        'nome' => $alt->nome,
                        'tipo' => $alt->tipo,
                        'ativo' => $alt->ativo ?? true,
                        'chave_canonica' => $alt->chave_canonica ?? null,
                        'empresa_id' => $alt->empresa_id ?? null,
                        'pivot' => [
                            'obrigatorio' => (bool) ($alt->pivot->obrigatorio ?? false),
                            'min' => $alt->pivot->min ?? null,
                            'max' => $alt->pivot->max ?? null,
                            'ordem' => $alt->pivot->ordem ?? 0,
                            'class_especial' => $alt->pivot->class_especial ?? null,
                        ],
                        'opcoes' => $opcoes->map(fn ($o) => [
                            'id' => $o->id,
                            'label' => $o->label,
                            'value' => $o->value,
                            'ordem' => $o->ordem,
                            'selecionado' => $o->selecionado,
                        ])->values()->all(),
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }
}
