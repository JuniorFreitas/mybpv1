<?php

namespace App\Services\ApiExterna;

use App\Models\Arquivo;
use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * API v3 Fornecedores (somente leitura): listagem/edit, busca, tipos de serviço,
 * serviços e anexos com links autenticados.
 */
class FornecedorApiService
{
    private const FORNECEDOR_COLS = [
        'id',
        'tipo',
        'tipo_pessoa',
        'nome',
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'cpf',
        'email',
        'contato',
        'aniversario',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'municipio',
        'uf',
        'ativo',
        'created_at',
        'updated_at',
    ];

    /**
     * @param  array{q?:string,tipo?:string,tipo_pessoa?:string,uf?:string,tipo_servico_id?:int}  $filtros
     */
    public function listar(
        int $empresaId,
        int $page,
        int $perPage,
        ?bool $ativo = null,
        array $filtros = []
    ): LengthAwarePaginator {
        $query = DB::table('fornecedores')
            ->select(self::FORNECEDOR_COLS)
            ->where('empresa_id', $empresaId)
            ->when($ativo !== null, fn ($q) => $q->where('ativo', $ativo))
            ->orderByDesc('id');

        $this->aplicarFiltros($query, $filtros);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $ids = collect($paginator->items())->pluck('id')->map(fn ($id) => (int) $id)->all();
        $telefones = $this->carregarTelefones($ids);
        $servicos = $this->carregarServicos($ids, comAnexos: false);

        return $paginator->through(function ($row) use ($telefones, $servicos) {
            return $this->mapFornecedor(
                $row,
                $telefones->get((int) $row->id) ?? collect(),
                $servicos->get((int) $row->id) ?? collect(),
                null
            );
        });
    }

    /**
     * @param  array{tipo?:string,tipo_pessoa?:string,ativo?:?bool}  $filtros
     */
    public function buscar(
        int $empresaId,
        string $q,
        int $page,
        int $perPage,
        array $filtros = []
    ): ?LengthAwarePaginator {
        $q = trim($q);
        if (mb_strlen($q) < 2) {
            return null;
        }

        return $this->listar(
            $empresaId,
            $page,
            $perPage,
            $filtros['ativo'] ?? null,
            array_merge($filtros, ['q' => $q])
        );
    }

    public function detalhe(int $empresaId, int $id): ?array
    {
        $row = DB::table('fornecedores')
            ->select(self::FORNECEDOR_COLS)
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->first();

        if (! $row) {
            return null;
        }

        $telefones = $this->carregarTelefones([$id]);
        $servicos = $this->carregarServicos([$id], comAnexos: true);
        $anexos = $this->carregarAnexosFornecedor([$id]);

        return $this->mapFornecedor(
            $row,
            $telefones->get($id) ?? collect(),
            $servicos->get($id) ?? collect(),
            $anexos->get($id) ?? collect()
        );
    }

    /**
     * Catálogo de tipos de serviço (como na listagem interna).
     *
     * @return list<array<string, mixed>>
     */
    public function tiposServico(?bool $ativo = true): array
    {
        if (! Schema::hasTable('tipo_servico_fornecedor')) {
            return [];
        }

        return DB::table('tipo_servico_fornecedor')
            ->when($ativo !== null, fn ($q) => $q->where('ativo', $ativo))
            ->orderBy('label')
            ->select(['id', 'label', 'ativo', 'created_at', 'updated_at'])
            ->get()
            ->map(fn ($t) => [
                'public_id' => ApiExternaId::encode((int) $t->id),
                'label' => $t->label,
                'ativo' => (bool) $t->ativo,
                'created_at' => $t->created_at,
                'updated_at' => $t->updated_at,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array{ativo?:?bool,status?:string,tipo_servico_id?:int}  $filtros
     * @return list<array<string, mixed>>|null
     */
    public function listarServicos(int $empresaId, int $fornecedorId, array $filtros = []): ?array
    {
        if (! $this->existeFornecedor($empresaId, $fornecedorId)) {
            return null;
        }

        $map = $this->carregarServicos([$fornecedorId], comAnexos: true);
        $lista = ($map->get($fornecedorId) ?? collect())->values();

        if (array_key_exists('ativo', $filtros) && $filtros['ativo'] !== null) {
            $lista = $lista->where('ativo', (bool) $filtros['ativo'])->values();
        }
        if (! empty($filtros['status'])) {
            $lista = $lista->where('status', $filtros['status'])->values();
        }
        if (! empty($filtros['tipo_servico_id'])) {
            $tipoPid = ApiExternaId::encode((int) $filtros['tipo_servico_id']);
            $lista = $lista->filter(
                fn ($s) => ($s['tipo_servico_public_id'] ?? null) === $tipoPid
            )->values();
        }

        return $lista->all();
    }

    public function servicoDetalhe(int $empresaId, int $fornecedorId, int $servicoId): ?array
    {
        if (! $this->existeFornecedor($empresaId, $fornecedorId)) {
            return null;
        }

        $map = $this->carregarServicos([$fornecedorId], comAnexos: true);
        $servico = ($map->get($fornecedorId) ?? collect())
            ->first(fn ($s) => ApiExternaId::decode($s['public_id'] ?? '') === $servicoId);

        return $servico ?: null;
    }

    /**
     * @return object{id:int,file:?string,thumb:?string,imagem:bool,nome:?string,extensao:?string,disco:?string}|null
     */
    public function resolverArquivoFornecedor(int $empresaId, int $fornecedorId, int $arquivoId): ?object
    {
        if (! $this->existeFornecedor($empresaId, $fornecedorId)) {
            return null;
        }

        if (! Schema::hasTable('fornecedor_anexos')) {
            return null;
        }

        $ligado = DB::table('fornecedor_anexos')
            ->where('fornecedor_id', $fornecedorId)
            ->where('arquivo_id', $arquivoId)
            ->exists();

        if (! $ligado) {
            return null;
        }

        return $this->carregarArquivoLinha($arquivoId, Arquivo::DISCO_FORNECEDOR);
    }

    /**
     * @return object{id:int,file:?string,thumb:?string,imagem:bool,nome:?string,extensao:?string,disco:?string}|null
     */
    public function resolverArquivoServico(
        int $empresaId,
        int $fornecedorId,
        int $servicoId,
        int $arquivoId
    ): ?object {
        if (! $this->existeFornecedor($empresaId, $fornecedorId)) {
            return null;
        }

        $servicoOk = DB::table('fornecedor_servico')
            ->where('id', $servicoId)
            ->where('fornecedor_id', $fornecedorId)
            ->exists();

        if (! $servicoOk || ! Schema::hasTable('fornecedor_servico_anexos')) {
            return null;
        }

        $ligado = DB::table('fornecedor_servico_anexos')
            ->where('fornecedor_servico_id', $servicoId)
            ->where('arquivo_id', $arquivoId)
            ->exists();

        if (! $ligado) {
            return null;
        }

        return $this->carregarArquivoLinha($arquivoId, Arquivo::DISCO_SERVICO_FORNECEDOR);
    }

    private function existeFornecedor(int $empresaId, int $id): bool
    {
        return DB::table('fornecedores')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->exists();
    }

    /**
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array{q?:string,tipo?:string,tipo_pessoa?:string,uf?:string,tipo_servico_id?:int}  $filtros
     */
    private function aplicarFiltros($query, array $filtros): void
    {
        if (! empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }
        if (! empty($filtros['tipo_pessoa'])) {
            $query->where('tipo_pessoa', $filtros['tipo_pessoa']);
        }
        if (! empty($filtros['uf'])) {
            $query->where('uf', $filtros['uf']);
        }

        $q = isset($filtros['q']) ? trim((string) $filtros['q']) : '';
        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $query->where(function ($inner) use ($like, $q) {
                $inner->where('nome', 'like', $like)
                    ->orWhere('razao_social', 'like', $like)
                    ->orWhere('nome_fantasia', 'like', $like)
                    ->orWhere('cnpj', 'like', $like)
                    ->orWhere('cpf', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('contato', 'like', $like);
                if (ctype_digit($q)) {
                    $inner->orWhere('id', (int) $q);
                }
            });
        }

        if (! empty($filtros['tipo_servico_id']) && Schema::hasTable('fornecedor_servico')) {
            $tipoId = (int) $filtros['tipo_servico_id'];
            $query->whereExists(function ($sub) use ($tipoId) {
                $sub->select(DB::raw(1))
                    ->from('fornecedor_servico as fs')
                    ->whereColumn('fs.fornecedor_id', 'fornecedores.id')
                    ->where('fs.tipo_servico_fornecedor_id', $tipoId);
            });
        }
    }

    /**
     * @param  list<int>  $fornecedorIds
     * @return Collection<int, Collection<int, object>>
     */
    private function carregarTelefones(array $fornecedorIds): Collection
    {
        if ($fornecedorIds === []) {
            return collect();
        }

        $porUsuario = Schema::hasTable('usuarios_telefone')
            ? DB::table('usuarios_telefone')
                ->whereIn('user_id', $fornecedorIds)
                ->select(['id', 'user_id', 'tipo', 'pais', 'numero', 'ramal', 'detalhe', 'principal'])
                ->orderBy('id')
                ->get()
                ->groupBy('user_id')
            : collect();

        $porFornecedor = Schema::hasTable('telefone_fornecedores')
            ? DB::table('telefone_fornecedores')
                ->whereIn('fornecedor_id', $fornecedorIds)
                ->select(['id', 'fornecedor_id', 'tipo', 'pais', 'numero', 'ramal', 'detalhe', 'principal'])
                ->orderBy('id')
                ->get()
                ->groupBy('fornecedor_id')
            : collect();

        $out = collect();
        foreach ($fornecedorIds as $fid) {
            $lista = collect($porUsuario->get($fid) ?? [])
                ->merge($porFornecedor->get($fid) ?? [])
                ->unique('id')
                ->values();
            $out->put($fid, $lista);
        }

        return $out;
    }

    /**
     * @param  list<int>  $fornecedorIds
     * @return Collection<int, Collection<int, array<string, mixed>>>
     */
    private function carregarServicos(array $fornecedorIds, bool $comAnexos): Collection
    {
        if ($fornecedorIds === [] || ! Schema::hasTable('fornecedor_servico')) {
            return collect();
        }

        $rows = DB::table('fornecedor_servico as fs')
            ->leftJoin('tipo_servico_fornecedor as ts', 'ts.id', '=', 'fs.tipo_servico_fornecedor_id')
            ->whereIn('fs.fornecedor_id', $fornecedorIds)
            ->orderByDesc('fs.id')
            ->select([
                'fs.id',
                'fs.fornecedor_id',
                'fs.tipo_servico_fornecedor_id',
                'fs.vencimento',
                'fs.data_inicio',
                'fs.data_encerramento',
                'fs.escopo',
                'fs.valor',
                'fs.tipo_faturamento',
                'fs.status',
                'fs.feedback',
                'fs.ativo',
                'ts.label as tipo_servico_label',
                'ts.ativo as tipo_servico_ativo',
            ])
            ->get();

        $anexosPorServico = $comAnexos
            ? $this->carregarAnexosServico($rows->pluck('id')->map(fn ($id) => (int) $id)->all())
            : collect();

        return $rows->groupBy('fornecedor_id')->map(function (Collection $group) use ($anexosPorServico, $comAnexos) {
            return $group->map(function ($s) use ($anexosPorServico, $comAnexos) {
                $fornecedorPid = ApiExternaId::encode((int) $s->fornecedor_id);
                $servicoPid = ApiExternaId::encode((int) $s->id);
                $base = url('/api/v3/fornecedores/' . $fornecedorPid . '/servicos/' . $servicoPid);

                $anexos = $comAnexos
                    ? ($anexosPorServico->get((int) $s->id) ?? collect())
                        ->map(fn ($a) => $this->comLinksAnexo($a, $base . '/anexos/' . $a['public_id']))
                        ->values()
                        ->all()
                    : null;

                $item = [
                    'public_id' => $servicoPid,
                    'fornecedor_public_id' => $fornecedorPid,
                    'tipo_servico_public_id' => ApiExternaId::encode($s->tipo_servico_fornecedor_id),
                    'tipo_servico' => $s->tipo_servico_fornecedor_id ? [
                        'public_id' => ApiExternaId::encode($s->tipo_servico_fornecedor_id),
                        'label' => $s->tipo_servico_label,
                        'ativo' => $s->tipo_servico_ativo,
                    ] : null,
                    'vencimento' => $s->vencimento,
                    'data_inicio' => $s->data_inicio,
                    'data_encerramento' => $s->data_encerramento,
                    'escopo' => $s->escopo,
                    'valor' => $s->valor,
                    'tipo_faturamento' => $s->tipo_faturamento,
                    'status' => $s->status,
                    'feedback' => $s->feedback,
                    'ativo' => (bool) $s->ativo,
                    'links' => [
                        'self' => $base,
                        'fornecedor' => url('/api/v3/fornecedores/' . $fornecedorPid),
                    ],
                ];

                if ($anexos !== null) {
                    $item['anexos'] = $anexos;
                }

                return $item;
            })->values();
        });
    }

    /**
     * @param  list<int>  $fornecedorIds
     * @return Collection<int, Collection<int, array<string, mixed>>>
     */
    private function carregarAnexosFornecedor(array $fornecedorIds): Collection
    {
        if ($fornecedorIds === [] || ! Schema::hasTable('fornecedor_anexos')) {
            return collect();
        }

        $rows = DB::table('fornecedor_anexos as fa')
            ->join('arquivos as a', 'a.id', '=', 'fa.arquivo_id')
            ->whereIn('fa.fornecedor_id', $fornecedorIds)
            ->select([
                'fa.fornecedor_id',
                'a.id',
                'a.nome',
                'a.extensao',
                'a.bytes',
                'a.created_at',
                'a.updated_at',
            ])
            ->orderBy('a.id')
            ->get();

        return $rows->groupBy('fornecedor_id')->map(function (Collection $group, $fornecedorId) {
            $fornecedorPid = ApiExternaId::encode((int) $fornecedorId);

            return $group->map(function ($a) use ($fornecedorPid) {
                $meta = $this->mapArquivoMeta($a);
                $base = url('/api/v3/fornecedores/' . $fornecedorPid . '/anexos/' . $meta['public_id']);

                return $this->comLinksAnexo($meta, $base);
            })->values();
        });
    }

    /**
     * @param  list<int>  $servicoIds
     * @return Collection<int, Collection<int, array<string, mixed>>>
     */
    private function carregarAnexosServico(array $servicoIds): Collection
    {
        if ($servicoIds === [] || ! Schema::hasTable('fornecedor_servico_anexos')) {
            return collect();
        }

        $rows = DB::table('fornecedor_servico_anexos as fsa')
            ->join('arquivos as a', 'a.id', '=', 'fsa.arquivo_id')
            ->whereIn('fsa.fornecedor_servico_id', $servicoIds)
            ->select([
                'fsa.fornecedor_servico_id',
                'a.id',
                'a.nome',
                'a.extensao',
                'a.bytes',
                'a.created_at',
                'a.updated_at',
            ])
            ->orderBy('a.id')
            ->get();

        return $rows->groupBy('fornecedor_servico_id')->map(
            fn (Collection $group) => $group->map(fn ($a) => $this->mapArquivoMeta($a))->values()
        );
    }

    private function mapArquivoMeta(object $arquivo): array
    {
        return [
            'public_id' => ApiExternaId::encode((int) $arquivo->id),
            'nome' => $arquivo->nome,
            'extensao' => $arquivo->extensao,
            'bytes' => $arquivo->bytes,
            'created_at' => $arquivo->created_at,
            'updated_at' => $arquivo->updated_at,
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function comLinksAnexo(array $meta, string $base): array
    {
        $meta['links'] = [
            'url' => $base . '/arquivo',
            'url_download' => $base . '/download',
            'urlDownload' => $base . '/download',
        ];
        $meta['url'] = $meta['links']['url'];
        $meta['url_download'] = $meta['links']['url_download'];
        $meta['urlDownload'] = $meta['links']['urlDownload'];

        return $meta;
    }

    private function carregarArquivoLinha(int $arquivoId, string $discoDefault): ?object
    {
        $cols = ['id', 'nome', 'extensao', 'file', 'thumb', 'disco'];
        if (Schema::hasColumn('arquivos', 'imagem')) {
            $cols[] = 'imagem';
        }

        $arquivo = DB::table('arquivos')
            ->where('id', $arquivoId)
            ->select($cols)
            ->first();

        if (! $arquivo || empty($arquivo->file)) {
            return null;
        }

        $arquivo->imagem = (bool) ($arquivo->imagem ?? false);
        if (empty($arquivo->disco)) {
            $arquivo->disco = $discoDefault;
        }

        return $arquivo;
    }

    /**
     * @param  Collection<int, object>  $telefones
     * @param  Collection<int, array<string, mixed>>  $servicos
     * @param  Collection<int, array<string, mixed>>|null  $anexos
     * @return array<string, mixed>
     */
    private function mapFornecedor(object $row, Collection $telefones, Collection $servicos, ?Collection $anexos): array
    {
        $data = ApiExternaId::mapRow($row, ['id'], ['empresa_id']);
        $publicId = $data['public_id'];
        $data['cpf'] = ApiExternaResponse::mascararCpf($data['cpf'] ?? null);
        $data['endereco_completo'] = $this->enderecoCompleto($row);
        $data['telefones'] = $telefones->map(fn ($t) => $this->mapTelefone($t))->values()->all();
        $data['servicos'] = $servicos->values()->all();
        $data['servicos_count'] = count($data['servicos']);
        $data['links'] = [
            'self' => url('/api/v3/fornecedores/' . $publicId),
            'servicos' => url('/api/v3/fornecedores/' . $publicId . '/servicos'),
        ];

        if ($anexos !== null) {
            $data['anexos'] = $anexos->values()->all();
            $data['anexos_count'] = count($data['anexos']);
        }

        return $data;
    }

    private function mapTelefone(object $tel): array
    {
        return [
            'public_id' => ApiExternaId::encode((int) $tel->id),
            'tipo' => $tel->tipo ?? null,
            'pais' => $tel->pais ?? null,
            'numero' => $tel->numero ?? null,
            'ramal' => $tel->ramal ?? null,
            'detalhe' => $tel->detalhe ?? null,
            'principal' => (bool) ($tel->principal ?? false),
        ];
    }

    private function enderecoCompleto(object $row): string
    {
        $partes = array_filter([
            $row->logradouro ?? null,
            $row->complemento ?? null,
            ($row->numero ?? null) ?: 'S/N',
            $row->bairro ?? null,
            $row->cep ?? null,
            trim(($row->municipio ?? '') . (($row->uf ?? null) ? '-' . $row->uf : ''), '-'),
        ]);

        return implode(', ', $partes);
    }
}
