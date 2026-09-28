<?php

namespace App\Services\Cih;

use App\Models\FeedbackCurriculo;
use Illuminate\Support\Collection;

/**
 * Monta payload enxuto de colaboradores CIH (modal editar/visualizar).
 */
class CihColaboradorPayloadMapper
{
    /**
     * @param Collection<int, FeedbackCurriculo> $colaboradores
     * @return list<array{id:int,nome:string,cargo:string,centro_custo:string,centro_custo_id:int|null,demitido:bool}>
     */
    public function mapForModal(Collection $colaboradores): array
    {
        return $colaboradores->map(function (FeedbackCurriculo $colaborador) {
            $demitido = $colaborador->relationLoaded('Demissao')
                ? $colaborador->Demissao !== null
                : false;

            $nome = (string) ($colaborador->Curriculo->nome ?? '');
            if ($demitido && $nome !== '') {
                $nome .= ' - Demitido(a)';
            }

            $admissao = $colaborador->Admissao;
            $centroCustoId = $admissao?->centro_custo_id;

            return [
                'id' => (int) $colaborador->id,
                'nome' => $nome,
                'cargo' => (string) ($admissao?->cargo ?? ''),
                'centro_custo' => (string) ($admissao?->CentroCusto?->label ?? ''),
                'centro_custo_id' => $centroCustoId !== null ? (int) $centroCustoId : null,
                'demitido' => $demitido,
            ];
        })->values()->all();
    }
}
