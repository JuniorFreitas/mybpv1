<?php

namespace App\Services\ApiExterna;

use App\Support\ApiExterna\ApiExternaId;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CentroCustoApiService
{
    /**
     * @param  array<string, mixed>  $filtros
     */
    public function listar(int $empresaId, int $page, int $perPage, array $filtros = []): LengthAwarePaginator
    {
        $query = DB::table('centro_custos as cc')
            ->where('cc.empresa_id', $empresaId)
            ->select(['cc.id', 'cc.label', 'cc.ativo', 'cc.gestor_id'])
            ->orderBy('cc.label');

        if (! empty($filtros['q'])) {
            $query->where('cc.label', 'like', '%' . trim((string) $filtros['q']) . '%');
        }

        if (array_key_exists('ativo', $filtros) && $filtros['ativo'] !== null) {
            $query->where('cc.ativo', (bool) $filtros['ativo']);
        }

        return $query->paginate($perPage, ['*'], 'page', $page)
            ->through(fn ($row) => $this->mapItem($row, detalhe: false));
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function buscar(int $empresaId, string $q, int $page, int $perPage, array $filtros = []): ?LengthAwarePaginator
    {
        $q = trim($q);
        if (mb_strlen($q) < 2) {
            return null;
        }

        $filtros['q'] = $q;

        return $this->listar($empresaId, $page, $perPage, $filtros);
    }

    public function detalhe(int $empresaId, int $id): ?array
    {
        $row = DB::table('centro_custos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->select(['id', 'label', 'ativo', 'gestor_id'])
            ->first();

        if (! $row) {
            return null;
        }

        $mapped = $this->mapItem($row, detalhe: true);
        $mapped['filiais'] = $this->filiais($empresaId, $id)['filiais'] ?? [];
        $mapped['gestores'] = $this->gestores($empresaId, $id)['gestores'] ?? [];

        return $mapped;
    }

    public function filiais(int $empresaId, int $centroId): ?array
    {
        if (! $this->existe($empresaId, $centroId)) {
            return null;
        }

        $pid = ApiExternaId::encode($centroId);
        $itens = [];

        if (Schema::hasTable('centro_custo_filials')) {
            $query = DB::table('centro_custo_filials as ccf')
                ->where('ccf.centro_custo_id', $centroId)
                ->whereNull('ccf.deleted_at')
                ->select(['ccf.id', 'ccf.ativo', 'ccf.cliente_filial_id'])
                ->orderBy('ccf.id');

            if (Schema::hasTable('cliente_filials')) {
                $query->leftJoin('cliente_filials as cf', function ($join) use ($empresaId) {
                    $join->on('cf.id', '=', 'ccf.cliente_filial_id')
                        ->where('cf.empresa_id', $empresaId)
                        ->whereNull('cf.deleted_at');
                })->addSelect(['cf.dados as filial_dados']);
            }

            $itens = $query->get()->map(function ($f) {
                $dados = $this->dadosFilial($f->filial_dados ?? null);

                return [
                    'public_id' => ApiExternaId::encode((int) $f->id),
                    'ativo' => isset($f->ativo) ? (bool) $f->ativo : null,
                    'cliente_filial_public_id' => ApiExternaId::encode($f->cliente_filial_id ?? null),
                    'nome' => $dados['nome_fantasia'] ?? $dados['razao_social'] ?? null,
                    'cnpj' => $dados['cnpj'] ?? null,
                ];
            })->all();
        }

        return [
            'public_id' => $pid,
            'filiais' => $itens,
            'links' => [
                'self' => url('/api/v3/centros-custo/' . $pid . '/filiais'),
                'centro_custo' => url('/api/v3/centros-custo/' . $pid),
            ],
        ];
    }

    public function gestores(int $empresaId, int $centroId): ?array
    {
        if (! $this->existe($empresaId, $centroId)) {
            return null;
        }

        $pid = ApiExternaId::encode($centroId);
        $itens = [];

        if (Schema::hasTable('centro_custo_gestores')) {
            $itens = DB::table('centro_custo_gestores as ccg')
                ->leftJoin('users as u', function ($join) {
                    $join->on('u.id', '=', 'ccg.usuario_id')->whereNull('u.deleted_at');
                })
                ->where('ccg.centro_custo_id', $centroId)
                ->when(Schema::hasColumn('centro_custo_gestores', 'ativo'), fn ($q) => $q->where('ccg.ativo', true))
                ->select(['ccg.id', 'ccg.usuario_id', 'ccg.tipo', 'u.nome'])
                ->orderBy('u.nome')
                ->get()
                ->map(fn ($g) => [
                    'public_id' => ApiExternaId::encode((int) $g->id),
                    'usuario' => $g->usuario_id ? [
                        'public_id' => ApiExternaId::encode((int) $g->usuario_id),
                        'nome' => $g->nome,
                    ] : null,
                    'tipo' => $g->tipo,
                ])
                ->all();
        }

        return [
            'public_id' => $pid,
            'gestores' => $itens,
            'links' => [
                'self' => url('/api/v3/centros-custo/' . $pid . '/gestores'),
                'centro_custo' => url('/api/v3/centros-custo/' . $pid),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dadosFilial(mixed $dados): array
    {
        if (is_string($dados) && $dados !== '') {
            $dados = json_decode($dados, true);
        } elseif (is_object($dados)) {
            $dados = (array) $dados;
        }

        return is_array($dados) ? $dados : [];
    }

    private function existe(int $empresaId, int $id): bool
    {
        return DB::table('centro_custos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->exists();
    }

    private function mapItem(object $row, bool $detalhe): array
    {
        $pid = ApiExternaId::encode((int) $row->id);

        $data = [
            'public_id' => $pid,
            'label' => $row->label,
            'ativo' => (bool) $row->ativo,
            'gestor_public_id' => ApiExternaId::encode($row->gestor_id ?? null),
            'links' => [
                'self' => url('/api/v3/centros-custo/' . $pid),
                'filiais' => url('/api/v3/centros-custo/' . $pid . '/filiais'),
                'gestores' => url('/api/v3/centros-custo/' . $pid . '/gestores'),
            ],
        ];

        if ($detalhe && $row->gestor_id) {
            $nome = DB::table('users')
                ->where('id', $row->gestor_id)
                ->whereNull('deleted_at')
                ->value('nome');
            $data['gestor'] = [
                'public_id' => ApiExternaId::encode((int) $row->gestor_id),
                'nome' => $nome,
            ];
        }

        return $data;
    }
}
