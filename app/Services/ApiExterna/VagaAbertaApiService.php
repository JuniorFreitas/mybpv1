<?php

namespace App\Services\ApiExterna;

use App\Support\ApiExterna\ApiExternaId;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VagaAbertaApiService
{
    /**
     * @param  array<string, mixed>  $filtros
     */
    public function listar(int $empresaId, int $page, int $perPage, array $filtros = []): LengthAwarePaginator
    {
        $query = DB::table('vagas_abertas as va')
            ->leftJoin('vagas as v', 'v.id', '=', 'va.vaga_id')
            ->leftJoin('municipios as m', 'm.id', '=', 'va.municipio_id')
            ->where('va.empresa_id', $empresaId)
            ->select([
                'va.id',
                'va.titulo',
                'va.descricao',
                'va.ativo',
                'va.ativo_sistema',
                'va.vaga_id',
                'va.municipio_id',
                'v.nome as vaga_nome',
                'm.nome as municipio_nome',
                'm.uf as municipio_uf',
            ])
            ->orderByDesc('va.id');

        $this->aplicarFiltros($query, $filtros);

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
        $row = DB::table('vagas_abertas as va')
            ->leftJoin('vagas as v', 'v.id', '=', 'va.vaga_id')
            ->leftJoin('municipios as m', 'm.id', '=', 'va.municipio_id')
            ->where('va.empresa_id', $empresaId)
            ->where('va.id', $id)
            ->select([
                'va.id',
                'va.titulo',
                'va.descricao',
                'va.ativo',
                'va.ativo_sistema',
                'va.vaga_id',
                'va.municipio_id',
                'v.nome as vaga_nome',
                'm.nome as municipio_nome',
                'm.uf as municipio_uf',
            ])
            ->first();

        if (! $row) {
            return null;
        }

        $mapped = $this->mapItem($row, detalhe: true);
        $mapped['simulados'] = $this->simulados($empresaId, $id)['simulados'] ?? [];
        $mapped['projetos'] = $this->projetos($empresaId, $id)['projetos'] ?? [];

        return $mapped;
    }

    public function simulados(int $empresaId, int $vagaAbertaId): ?array
    {
        if (! $this->existe($empresaId, $vagaAbertaId)) {
            return null;
        }

        $pid = ApiExternaId::encode($vagaAbertaId);
        $itens = [];

        if (Schema::hasTable('simulado_vagas')) {
            $itens = DB::table('simulado_vagas')
                ->where('empresa_id', $empresaId)
                ->where('vagas_abertas_id', $vagaAbertaId)
                ->select(['id', 'simulado_id', 'data_inicio', 'data_fim', 'duracao', 'online', 'ativo'])
                ->orderByDesc('id')
                ->get()
                ->map(fn ($s) => [
                    'public_id' => ApiExternaId::encode((int) $s->id),
                    'simulado_public_id' => ApiExternaId::encode($s->simulado_id),
                    'data_inicio' => $s->data_inicio,
                    'data_fim' => $s->data_fim,
                    'duracao' => $s->duracao,
                    'online' => (bool) $s->online,
                    'ativo' => (bool) $s->ativo,
                ])
                ->all();
        }

        return [
            'public_id' => $pid,
            'simulados' => $itens,
            'links' => [
                'self' => url('/api/v3/vagas-abertas/' . $pid . '/simulados'),
                'vaga_aberta' => url('/api/v3/vagas-abertas/' . $pid),
            ],
        ];
    }

    public function projetos(int $empresaId, int $vagaAbertaId): ?array
    {
        if (! $this->existe($empresaId, $vagaAbertaId)) {
            return null;
        }

        $pid = ApiExternaId::encode($vagaAbertaId);
        $itens = [];

        if (Schema::hasTable('vaga_projetos')) {
            $itens = DB::table('vaga_projetos as vp')
                ->leftJoin('projetos as p', 'p.id', '=', 'vp.projeto_id')
                ->where('vp.empresa_id', $empresaId)
                ->where('vp.vaga_aberta_id', $vagaAbertaId)
                ->select(['vp.id', 'vp.projeto_id', 'vp.qnt_total', 'vp.qnt_preenchida', 'p.nome as projeto_nome'])
                ->orderByDesc('vp.id')
                ->get()
                ->map(fn ($p) => [
                    'public_id' => ApiExternaId::encode((int) $p->id),
                    'projeto_public_id' => ApiExternaId::encode($p->projeto_id),
                    'projeto_nome' => $p->projeto_nome,
                    'qnt_total' => (int) $p->qnt_total,
                    'qnt_preenchida' => (int) $p->qnt_preenchida,
                ])
                ->all();
        }

        return [
            'public_id' => $pid,
            'projetos' => $itens,
            'links' => [
                'self' => url('/api/v3/vagas-abertas/' . $pid . '/projetos'),
                'vaga_aberta' => url('/api/v3/vagas-abertas/' . $pid),
            ],
        ];
    }

    private function existe(int $empresaId, int $id): bool
    {
        return DB::table('vagas_abertas')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    private function aplicarFiltros($query, array $filtros): void
    {
        if (! empty($filtros['q'])) {
            $q = trim((string) $filtros['q']);
            $query->where(function ($inner) use ($q) {
                $inner->where('va.titulo', 'like', '%' . $q . '%')
                    ->orWhere('v.nome', 'like', '%' . $q . '%')
                    ->orWhere('m.nome', 'like', '%' . $q . '%');
            });
        }

        if (array_key_exists('ativo', $filtros) && $filtros['ativo'] !== null) {
            $query->where('va.ativo', (bool) $filtros['ativo']);
        }

        if (array_key_exists('ativo_sistema', $filtros) && $filtros['ativo_sistema'] !== null) {
            $query->where('va.ativo_sistema', (bool) $filtros['ativo_sistema']);
        }

        if (! empty($filtros['vaga_id'])) {
            $query->where('va.vaga_id', (int) $filtros['vaga_id']);
        }

        if (! empty($filtros['municipio_id'])) {
            $query->where('va.municipio_id', (int) $filtros['municipio_id']);
        }

        if (! empty($filtros['uf'])) {
            $query->where('m.uf', strtoupper((string) $filtros['uf']));
        }
    }

    private function mapItem(object $row, bool $detalhe): array
    {
        $pid = ApiExternaId::encode((int) $row->id);

        $data = [
            'public_id' => $pid,
            'titulo' => $row->titulo,
            'ativo' => (bool) $row->ativo,
            'ativo_sistema' => (bool) $row->ativo_sistema,
            'vaga' => $row->vaga_id ? [
                'public_id' => ApiExternaId::encode((int) $row->vaga_id),
                'nome' => $row->vaga_nome,
            ] : null,
            'municipio' => $row->municipio_id ? [
                'public_id' => ApiExternaId::encode((int) $row->municipio_id),
                'nome' => $row->municipio_nome,
                'uf' => $row->municipio_uf,
            ] : null,
            'links' => [
                'self' => url('/api/v3/vagas-abertas/' . $pid),
                'simulados' => url('/api/v3/vagas-abertas/' . $pid . '/simulados'),
                'projetos' => url('/api/v3/vagas-abertas/' . $pid . '/projetos'),
            ],
        ];

        if ($detalhe) {
            $data['descricao'] = $row->descricao;
        }

        return $data;
    }
}
