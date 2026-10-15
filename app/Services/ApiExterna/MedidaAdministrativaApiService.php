<?php

namespace App\Services\ApiExterna;

use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MedidaAdministrativaApiService
{
    /**
     * @param  array<string, mixed>  $filtros
     */
    public function listar(int $empresaId, int $page, int $perPage, array $filtros = []): LengthAwarePaginator
    {
        $query = DB::table('medida_administrativas as m')
            ->join('feedback_curriculos as fc', function ($join) use ($empresaId) {
                $join->on('fc.id', '=', 'm.feedback_id')
                    ->where('fc.empresa_id', $empresaId)
                    ->whereNull('fc.deleted_at');
            })
            ->leftJoin('curriculos as c', function ($join) {
                $join->on('c.id', '=', 'fc.curriculo_id')->whereNull('c.deleted_at');
            })
            ->leftJoin('admissoes as a', function ($join) {
                $join->on('a.feedback_id', '=', 'fc.id')->whereNull('a.deleted_at');
            })
            ->whereNull('m.deleted_at')
            ->select([
                'm.id',
                'm.feedback_id',
                'm.tipo',
                'm.definicao',
                'm.causa',
                'm.data_solicitacao',
                'm.data_retorno',
                'm.solicitante',
                'c.nome as colaborador_nome',
                'c.cpf as colaborador_cpf',
                'a.matricula',
                'a.cargo',
            ])
            ->orderByDesc('m.id');

        $this->aplicarFiltros($query, $filtros);

        return $query->paginate($perPage, ['*'], 'page', $page)
            ->through(fn ($row) => $this->mapItem($row, detalhe: false));
    }

    public function detalhe(int $empresaId, int $id): ?array
    {
        $row = DB::table('medida_administrativas as m')
            ->join('feedback_curriculos as fc', function ($join) use ($empresaId) {
                $join->on('fc.id', '=', 'm.feedback_id')
                    ->where('fc.empresa_id', $empresaId)
                    ->whereNull('fc.deleted_at');
            })
            ->leftJoin('curriculos as c', function ($join) {
                $join->on('c.id', '=', 'fc.curriculo_id')->whereNull('c.deleted_at');
            })
            ->leftJoin('admissoes as a', function ($join) {
                $join->on('a.feedback_id', '=', 'fc.id')->whereNull('a.deleted_at');
            })
            ->where('m.id', $id)
            ->whereNull('m.deleted_at')
            ->select([
                'm.id',
                'm.feedback_id',
                'm.tipo',
                'm.definicao',
                'm.motivo',
                'm.causa',
                'm.data_solicitacao',
                'm.data_retorno',
                'm.solicitante',
                'c.nome as colaborador_nome',
                'c.cpf as colaborador_cpf',
                'a.matricula',
                'a.cargo',
            ])
            ->first();

        if (! $row) {
            return null;
        }

        $mapped = $this->mapItem($row, detalhe: true);
        $mapped['anexos_count'] = $this->contarAnexos((int) $row->id);

        return $mapped;
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    private function aplicarFiltros($query, array $filtros): void
    {
        if (! empty($filtros['q'])) {
            $q = trim((string) $filtros['q']);
            $query->where('c.nome', 'like', '%' . $q . '%');
        }

        if (! empty($filtros['tipo'])) {
            $query->where('m.tipo', $filtros['tipo']);
        }

        if (! empty($filtros['causa'])) {
            $query->where('m.causa', 'like', '%' . $filtros['causa'] . '%');
        }

        if (! empty($filtros['data_solicitacao_de'])) {
            $query->whereDate('m.data_solicitacao', '>=', $filtros['data_solicitacao_de']);
        }

        if (! empty($filtros['data_solicitacao_ate'])) {
            $query->whereDate('m.data_solicitacao', '<=', $filtros['data_solicitacao_ate']);
        }

        if (! empty($filtros['centro_custo_id'])) {
            $query->where('a.centro_custo_id', (int) $filtros['centro_custo_id']);
        }
    }

    private function mapItem(object $row, bool $detalhe): array
    {
        $pid = ApiExternaId::encode((int) $row->id);
        $processoPid = ApiExternaId::encode((int) $row->feedback_id);

        $data = [
            'public_id' => $pid,
            'processo_public_id' => $processoPid,
            'tipo' => $row->tipo,
            'definicao' => $row->definicao,
            'causa' => $row->causa,
            'data_solicitacao' => $row->data_solicitacao,
            'data_retorno' => $row->data_retorno,
            'solicitante' => $row->solicitante,
            'colaborador' => [
                'nome' => $row->colaborador_nome,
                'cpf' => ApiExternaResponse::mascararCpf($row->colaborador_cpf),
                'matricula' => $row->matricula,
                'cargo' => $row->cargo,
            ],
            'links' => [
                'self' => url('/api/v3/medidas-administrativas/' . $pid),
                'processo' => url('/api/v3/processos/' . $processoPid),
                'historico' => url('/api/v3/historico/' . $processoPid),
            ],
        ];

        if ($detalhe) {
            $data['motivo'] = $row->motivo;
        }

        return $data;
    }

    private function contarAnexos(int $medidaId): int
    {
        if (! Schema::hasTable('medida_evidencia')) {
            return 0;
        }

        return (int) DB::table('medida_evidencia')->where('medida_id', $medidaId)->count();
    }
}
