<?php

namespace App\Services\AtaReuniao;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AtaReuniaoRelatorioExportService
{
    public function dados(User $user, array $filtros = []): array
    {
        $empresaId = (int) $user->empresa_id;
        $dataInicio = $filtros['data_inicio'] ?? null;
        $dataFim = $filtros['data_fim'] ?? null;

        $rows = DB::table('ata_reuniaos as ata')
            ->leftJoin('ata_reuniao_acaos as acao', function ($join) {
                $join->on('acao.ata_reuniao_id', '=', 'ata.id')
                    ->whereNull('acao.deleted_at');
            })
            ->leftJoin('users as responsavel', 'responsavel.id', '=', 'acao.responsavel_id')
            ->where('ata.empresa_id', $empresaId)
            ->whereNull('ata.deleted_at')
            ->when($dataInicio, fn ($query) => $query->whereDate('ata.data_inicio', '>=', $dataInicio))
            ->when($dataFim, fn ($query) => $query->whereDate('ata.data_inicio', '<=', $dataFim))
            ->select([
                'ata.codigo',
                'ata.titulo',
                'ata.status as ata_status',
                'ata.data_inicio',
                'ata.data_fim',
                'ata.classificacao_confidencialidade',
                'acao.id as pendencia_id',
                'acao.titulo as pendencia_titulo',
                'acao.status as pendencia_status',
                'acao.prioridade',
                'acao.prazo',
                'responsavel.nome as responsavel_nome',
            ])
            ->orderByDesc('ata.data_inicio')
            ->orderBy('acao.prazo')
            ->get();

        return $rows->map(fn ($row) => [
            'codigo_ata' => $row->codigo ?: 'Nao informado',
            'titulo_ata' => $row->titulo ?: 'Ata de Reuniao',
            'status_ata' => $row->ata_status ?: 'rascunho',
            'data_inicio' => $row->data_inicio ?: 'Nao informado',
            'data_fim' => $row->data_fim ?: 'Nao informado',
            'confidencialidade' => $row->classificacao_confidencialidade ?: 'uso_interno',
            'codigo_pendencia' => $row->pendencia_id ? 'PEND-' . str_pad((string) $row->pendencia_id, 6, '0', STR_PAD_LEFT) : 'Nao informado',
            'pendencia' => $row->pendencia_titulo ?: 'Nao informado',
            'status_pendencia' => $row->pendencia_status ?: 'Nao informado',
            'prioridade' => $row->prioridade ?: 'Nao informado',
            'prazo' => $row->prazo ?: 'Nao informado',
            'responsavel' => $row->responsavel_nome ?: 'Nao informado',
        ])->all();
    }

    public function headers(): array
    {
        return [
            'Codigo da ata',
            'Titulo da ata',
            'Status da ata',
            'Data inicio',
            'Data fim',
            'Confidencialidade',
            'Codigo da pendencia',
            'Pendencia',
            'Status da pendencia',
            'Prioridade',
            'Prazo',
            'Responsavel',
        ];
    }

    public function rows(array $dados): array
    {
        return array_map(fn ($row) => array_values($row), $dados);
    }
}
