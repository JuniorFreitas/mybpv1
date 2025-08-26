<?php

namespace App\Services\Entrevistas;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ParecerRotaService
{
    /**
     * Build query with filters using ParecerRotaFilter
     */
    public function buildQuery(Request $request): Builder
    {
        return ParecerRotaFilter::make()
            ->apply($request)
            ->getQuery();
    }

    /**
     * Build query for specific user (for jobs)
     */
    public function buildQueryForUser(int $userId, array $filters): Builder
    {
        return ParecerRotaFilter::forUser($userId)
            ->apply($filters)
            ->getQuery();
    }

    /**
     * Get export data
     */
    public function getExportData(Request $request): \Illuminate\Support\Collection
    {
        return $this->buildQuery($request)->get();
    }

    /**
     * Build export rows data
     */
    public function buildExportRows(\Illuminate\Support\Collection $data): array
    {
        $rows = [];

        foreach ($data as $row) {
            $rows[] = [
                'nome' => $row->Curriculo->nome ?? '',
                'vaga' => $row->vaga_aberta_municipio ?? '',
                'pcd' => ($row->Curriculo->pcd ?? false) ? "SIM" : "NÃO", // ✅ CORRIGIDO O BUG
                'parecer_rh_nota' => $row->parecerRh->nota ?? '',
                'observacao' => $row->obs ?? '',
                'email' => $row->Curriculo->email ?? '',
                'bairro' => $row->Curriculo->bairro ?? '',
                'cep' => $row->Curriculo->cep ?? '',
                'endereco' => $row->Curriculo->logradouro ?? '',
                'municipio' => $row->Curriculo->municipio ?? '',
                'estado' => $row->Curriculo->uf ?? '',
                'complemento' => $row->Curriculo->complemento ?? '',
            ];
        }

        return $rows;
    }

    /**
     * Get statistics summary
     */
    public function getStatistics(Request $request): array
    {
        $query = $this->buildQuery($request);

        $total = $query->count();
        $comRota = $query->whereHas('parecerRota', function ($q) {
            $q->where('tem_rota', true);
        })->count();

        $semRota = $total - $comRota;

        $pcdTotal = $query->whereHas('Curriculo', function ($q) {
            $q->where('pcd', true);
        })->count();

        return [
            'total' => $total,
            'com_rota' => $comRota,
            'sem_rota' => $semRota,
            'pcd_total' => $pcdTotal,
            'percentual_com_rota' => $total > 0 ? round(($comRota / $total) * 100, 2) : 0,
            'percentual_pcd' => $total > 0 ? round(($pcdTotal / $total) * 100, 2) : 0,
        ];
    }
}
