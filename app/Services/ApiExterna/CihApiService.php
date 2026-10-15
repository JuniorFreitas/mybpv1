<?php

namespace App\Services\ApiExterna;

use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CihApiService
{
    /**
     * @param  array<string, mixed>  $filtros
     */
    public function listar(int $empresaId, int $page, int $perPage, array $filtros = []): LengthAwarePaginator
    {
        $query = DB::table('cihs as c')
            ->leftJoin('cih_tags as t', 't.id', '=', 'c.tag_id')
            ->leftJoin('areas as a', 'a.id', '=', 'c.area_id')
            ->leftJoin('users as g', function ($join) {
                $join->on('g.id', '=', 'c.gestor_id')->whereNull('g.deleted_at');
            })
            ->where('c.empresa_id', $empresaId)
            ->whereNull('c.deleted_at')
            ->select([
                'c.id',
                'c.status',
                'c.data_lancamento',
                'c.acao',
                'c.varios_colaboradores',
                'c.tag_id',
                'c.area_id',
                'c.gestor_id',
                'c.centro_custo_id',
                'c.cliente_id',
                't.label as tag_label',
                'a.label as area_label',
                'g.nome as gestor_nome',
            ])
            ->orderByDesc('c.id');

        $this->aplicarFiltros($query, $filtros);

        return $query->paginate($perPage, ['*'], 'page', $page)
            ->through(fn ($row) => $this->mapItem($row, detalhe: false));
    }

    public function detalhe(int $empresaId, int $id): ?array
    {
        $row = DB::table('cihs as c')
            ->leftJoin('cih_tags as t', 't.id', '=', 'c.tag_id')
            ->leftJoin('areas as a', 'a.id', '=', 'c.area_id')
            ->leftJoin('users as g', function ($join) {
                $join->on('g.id', '=', 'c.gestor_id')->whereNull('g.deleted_at');
            })
            ->where('c.empresa_id', $empresaId)
            ->where('c.id', $id)
            ->whereNull('c.deleted_at')
            ->select([
                'c.id',
                'c.status',
                'c.data_lancamento',
                'c.data_aprovacao',
                'c.data_aprovacao_rh',
                'c.acao',
                'c.obs_lancamento',
                'c.obs_aprovacao',
                'c.obs_rh',
                'c.resposta_rh',
                'c.varios_colaboradores',
                'c.colaboradores_avulso',
                'c.outra_tag',
                'c.outra_area',
                'c.tag_id',
                'c.area_id',
                'c.gestor_id',
                'c.centro_custo_id',
                'c.cliente_id',
                'c.feedback_id',
                't.label as tag_label',
                'a.label as area_label',
                'g.nome as gestor_nome',
            ])
            ->first();

        if (! $row) {
            return null;
        }

        $mapped = $this->mapItem($row, detalhe: true);
        $mapped['colaboradores'] = $this->colaboradores($empresaId, $id)['colaboradores'] ?? [];

        return $mapped;
    }

    public function tags(int $empresaId, ?bool $ativo = true): array
    {
        if (! Schema::hasTable('cih_tags')) {
            return [];
        }

        return DB::table('cih_tags')
            ->select(['id', 'label', 'ativo'])
            ->when(
                Schema::hasColumn('cih_tags', 'empresa_id'),
                fn ($q) => $q->where('empresa_id', $empresaId)
            )
            ->when($ativo !== null && Schema::hasColumn('cih_tags', 'ativo'), fn ($q) => $q->where('ativo', $ativo))
            ->orderBy('label')
            ->get()
            ->map(fn ($t) => [
                'public_id' => ApiExternaId::encode((int) $t->id),
                'label' => $t->label,
                'ativo' => isset($t->ativo) ? (bool) $t->ativo : null,
            ])
            ->all();
    }

    public function colaboradores(int $empresaId, int $cihId): ?array
    {
        if (! $this->existe($empresaId, $cihId)) {
            return null;
        }

        $pid = ApiExternaId::encode($cihId);
        $itens = [];

        if (Schema::hasTable('cih_feedback')) {
            $itens = DB::table('cih_feedback as cf')
                ->join('feedback_curriculos as fc', function ($join) use ($empresaId) {
                    $join->on('fc.id', '=', 'cf.feedback_id')
                        ->where('fc.empresa_id', $empresaId)
                        ->whereNull('fc.deleted_at');
                })
                ->leftJoin('curriculos as cur', function ($join) {
                    $join->on('cur.id', '=', 'fc.curriculo_id')->whereNull('cur.deleted_at');
                })
                ->where('cf.cih_id', $cihId)
                ->select(['fc.id', 'cur.nome', 'cur.cpf'])
                ->orderBy('cur.nome')
                ->get()
                ->map(fn ($c) => [
                    'processo_public_id' => ApiExternaId::encode((int) $c->id),
                    'nome' => $c->nome,
                    'cpf' => ApiExternaResponse::mascararCpf($c->cpf),
                    'links' => [
                        'processo' => url('/api/v3/processos/' . ApiExternaId::encode((int) $c->id)),
                    ],
                ])
                ->all();
        }

        return [
            'public_id' => $pid,
            'colaboradores' => $itens,
            'links' => [
                'self' => url('/api/v3/cih/' . $pid . '/colaboradores'),
                'cih' => url('/api/v3/cih/' . $pid),
            ],
        ];
    }

    private function existe(int $empresaId, int $id): bool
    {
        return DB::table('cihs')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    private function aplicarFiltros($query, array $filtros): void
    {
        if (! empty($filtros['status'])) {
            $query->where('c.status', $filtros['status']);
        }

        if (! empty($filtros['tag_id'])) {
            $query->where('c.tag_id', (int) $filtros['tag_id']);
        }

        if (! empty($filtros['area_id'])) {
            $query->where('c.area_id', (int) $filtros['area_id']);
        }

        if (! empty($filtros['centro_custo_id'])) {
            $query->where('c.centro_custo_id', (int) $filtros['centro_custo_id']);
        }

        if (! empty($filtros['gestor_id'])) {
            $query->where('c.gestor_id', (int) $filtros['gestor_id']);
        }

        if (! empty($filtros['data_lancamento_de'])) {
            $query->whereDate('c.data_lancamento', '>=', $filtros['data_lancamento_de']);
        }

        if (! empty($filtros['data_lancamento_ate'])) {
            $query->whereDate('c.data_lancamento', '<=', $filtros['data_lancamento_ate']);
        }

        if (! empty($filtros['q'])) {
            $q = trim((string) $filtros['q']);
            $query->where(function ($inner) use ($q) {
                $inner->where('t.label', 'like', '%' . $q . '%')
                    ->orWhere('a.label', 'like', '%' . $q . '%')
                    ->orWhere('g.nome', 'like', '%' . $q . '%')
                    ->orWhere('c.acao', 'like', '%' . $q . '%');
            });
        }
    }

    private function mapItem(object $row, bool $detalhe): array
    {
        $pid = ApiExternaId::encode((int) $row->id);

        $data = [
            'public_id' => $pid,
            'status' => $row->status,
            'data_lancamento' => $row->data_lancamento,
            'acao' => $row->acao,
            'varios_colaboradores' => (bool) ($row->varios_colaboradores ?? false),
            'tag' => $row->tag_id ? [
                'public_id' => ApiExternaId::encode((int) $row->tag_id),
                'label' => $row->tag_label,
            ] : null,
            'area' => $row->area_id ? [
                'public_id' => ApiExternaId::encode((int) $row->area_id),
                'label' => $row->area_label,
            ] : null,
            'gestor' => $row->gestor_id ? [
                'public_id' => ApiExternaId::encode((int) $row->gestor_id),
                'nome' => $row->gestor_nome,
            ] : null,
            'centro_custo_public_id' => ApiExternaId::encode($row->centro_custo_id ?? null),
            'empresa_public_id' => ApiExternaId::encode($row->cliente_id ?? null),
            'links' => [
                'self' => url('/api/v3/cih/' . $pid),
                'colaboradores' => url('/api/v3/cih/' . $pid . '/colaboradores'),
            ],
        ];

        if ($detalhe) {
            $data['data_aprovacao'] = $row->data_aprovacao ?? null;
            $data['data_aprovacao_rh'] = $row->data_aprovacao_rh ?? null;
            $data['obs_lancamento'] = $row->obs_lancamento ?? null;
            $data['obs_aprovacao'] = $row->obs_aprovacao ?? null;
            $data['obs_rh'] = $row->obs_rh ?? null;
            $data['resposta_rh'] = $row->resposta_rh ?? null;
            $data['outra_tag'] = $row->outra_tag ?? null;
            $data['outra_area'] = $row->outra_area ?? null;
            $data['colaboradores_avulso'] = $row->colaboradores_avulso ?? null;
            $data['processo_public_id'] = ApiExternaId::encode($row->feedback_id ?? null);
        }

        return $data;
    }
}
