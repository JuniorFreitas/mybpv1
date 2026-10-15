<?php

namespace App\Services\ApiExterna;

use App\Models\Cloud;
use App\Models\GrupoCloud;
use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Payload alinhado ao Cloud interno (somente leitura):
 * listagem, edit, navegação, busca, árvore de pastas, path e arquivos.
 */
class CloudApiService
{
    private const ITEM_COLS = [
        'ic.id',
        'ic.cloud_id',
        'ic.arquivo_id',
        'ic.label',
        'ic.tipo',
        'ic.pertence',
        'ic.pertence_anterior',
        'ic.quem_criou',
        'ic.quem_editou',
        'ic.quem_aprovou',
        'ic.quem_revisou',
        'ic.quem_moveu',
        'ic.quem_excluiu',
        'ic.aprovado',
        'ic.data_aprovacao',
        'ic.revisado',
        'ic.data_revisao',
        'ic.movido',
        'ic.data_movido',
        'ic.created_at',
        'ic.updated_at',
    ];

    public function listar(
        int $empresaId,
        int $page,
        int $perPage,
        ?bool $ativo = null,
        ?string $q = null
    ): LengthAwarePaginator {
        $query = DB::table('clouds as c')
            ->where('c.empresa_id', $empresaId)
            ->when($ativo !== null, fn ($qb) => $qb->where('c.ativo', $ativo))
            ->when($q !== null && $q !== '', function ($qb) use ($q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $qb->where(function ($inner) use ($like) {
                    $inner->where('c.nome', 'like', $like)
                        ->orWhere('c.slug', 'like', $like);
                });
            })
            ->select([
                'c.id',
                'c.nome',
                'c.slug',
                'c.ativo',
                'c.created_at',
                'c.updated_at',
            ])
            ->orderBy('c.nome');

        if (Schema::hasTable('user_clouds')) {
            $query->selectSub(
                DB::table('user_clouds as uc')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('uc.cloud_id', 'c.id'),
                'usuarios_count'
            );
        }

        if (Schema::hasTable('itens_cloud')) {
            $query->selectSub(
                DB::table('itens_cloud as ic')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('ic.cloud_id', 'c.id')
                    ->whereNull('ic.deleted_at'),
                'itens_count'
            );
        }

        return $query
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($row) {
                $data = ApiExternaId::mapRow($row, ['id'], ['empresa_id']);
                $data['usuarios_count'] = (int) ($row->usuarios_count ?? 0);
                $data['itens_count'] = (int) ($row->itens_count ?? 0);

                return $data;
            });
    }

    public function detalhe(int $empresaId, int $cloudId): ?array
    {
        $row = DB::table('clouds')
            ->select(['id', 'nome', 'slug', 'ativo', 'created_at', 'updated_at'])
            ->where('empresa_id', $empresaId)
            ->where('id', $cloudId)
            ->first();

        if (! $row) {
            return null;
        }

        $data = ApiExternaId::mapRow($row, ['id'], ['empresa_id']);
        $data['usuarios'] = $this->carregarMembros($empresaId, $cloudId);
        $data['grupos'] = $this->carregarGruposEmpresa($empresaId);
        $data['usuarios_count'] = count($data['usuarios']);
        $data['itens_count'] = (int) DB::table('itens_cloud')
            ->where('cloud_id', $cloudId)
            ->whereNull('deleted_at')
            ->count();

        return $data;
    }

    /**
     * @param  int|null  $pastaId  Pasta pai (null = raiz). Ignorado se $filtros['recursivo'] ou busca global.
     * @param  array{tipo?:string,aprovado?:bool,revisado?:bool,q?:string,recursivo?:bool}  $filtros
     */
    public function itens(
        int $empresaId,
        int $cloudId,
        int $page,
        int $perPage,
        ?int $pastaId = null,
        array $filtros = []
    ): ?LengthAwarePaginator {
        if (! $this->existeCloud($empresaId, $cloudId)) {
            return null;
        }

        $recursivo = (bool) ($filtros['recursivo'] ?? false);
        $busca = isset($filtros['q']) ? trim((string) $filtros['q']) : '';

        if ($pastaId !== null && ! $recursivo && $busca === '') {
            if (! $this->pastaExiste($cloudId, $pastaId)) {
                return null;
            }
        }

        $query = DB::table('itens_cloud as ic')
            ->where('ic.cloud_id', $cloudId)
            ->whereNull('ic.deleted_at')
            ->select(self::ITEM_COLS)
            ->orderBy('ic.tipo')
            ->orderBy('ic.label');

        if ($busca !== '') {
            $like = '%' . addcslashes($busca, '%_\\') . '%';
            $query->where('ic.label', 'like', $like);
            if ($pastaId !== null && ! $recursivo) {
                $query->where('ic.pertence', $pastaId);
            }
        } elseif (! $recursivo) {
            $query->when(
                $pastaId === null,
                fn ($q) => $q->whereNull('ic.pertence'),
                fn ($q) => $q->where('ic.pertence', $pastaId)
            );
        } elseif ($pastaId !== null) {
            // Recursivo sob uma pasta: itens cujo caminho passa por ela (descendentes).
            $descendentes = $this->idsDescendentes($cloudId, $pastaId);
            $query->whereIn('ic.pertence', $descendentes->prepend($pastaId)->all());
        }

        if (! empty($filtros['tipo']) && in_array($filtros['tipo'], ['pasta', 'arquivo'], true)) {
            $query->where('ic.tipo', $filtros['tipo']);
        }
        if (array_key_exists('aprovado', $filtros) && $filtros['aprovado'] !== null) {
            $query->where('ic.aprovado', (bool) $filtros['aprovado']);
        }
        if (array_key_exists('revisado', $filtros) && $filtros['revisado'] !== null) {
            $query->where('ic.revisado', (bool) $filtros['revisado']);
        }

        $paginator = $query->paginate(
            ApiExternaResponse::perPage($perPage),
            ['*'],
            'page',
            $page
        );

        $mapped = $this->mapearColecaoItens(collect($paginator->items()), $cloudId)
            ->keyBy(fn ($item) => ApiExternaId::decode($item['public_id']));

        return $paginator->through(function ($row) use ($mapped) {
            return $mapped->get((int) $row->id);
        });
    }

    public function itemDetalhe(int $empresaId, int $cloudId, int $itemId): ?array
    {
        if (! $this->existeCloud($empresaId, $cloudId)) {
            return null;
        }

        $row = DB::table('itens_cloud as ic')
            ->where('ic.cloud_id', $cloudId)
            ->where('ic.id', $itemId)
            ->whereNull('ic.deleted_at')
            ->select(self::ITEM_COLS)
            ->first();

        if (! $row) {
            return null;
        }

        $item = $this->mapearColecaoItens(collect([$row]), $cloudId)->first();
        $item['caminho'] = $this->montarCaminho($cloudId, $itemId);
        $item['path'] = collect($item['caminho'])->pluck('slug')->implode('/');

        return $item;
    }

    /**
     * Busca global no Cloud (label), espelhando /cloud/{slug}/buscar.
     *
     * @param  array{tipo?:string}  $filtros
     */
    public function buscar(
        int $empresaId,
        int $cloudId,
        string $q,
        int $page,
        int $perPage,
        array $filtros = []
    ): ?LengthAwarePaginator {
        $q = trim($q);
        if ($q === '' || mb_strlen($q) < 2) {
            return null;
        }

        return $this->itens($empresaId, $cloudId, $page, $perPage, null, array_merge($filtros, [
            'q' => $q,
            'recursivo' => true,
        ]));
    }

    /**
     * Árvore/navegação só de pastas (como estrutura-mover).
     *
     * @return list<array<string, mixed>>|null
     */
    public function pastas(int $empresaId, int $cloudId, ?int $pastaId = null): ?array
    {
        if (! $this->existeCloud($empresaId, $cloudId)) {
            return null;
        }

        if ($pastaId !== null && ! $this->pastaExiste($cloudId, $pastaId)) {
            return null;
        }

        $rows = DB::table('itens_cloud as ic')
            ->where('ic.cloud_id', $cloudId)
            ->where('ic.tipo', 'pasta')
            ->whereNull('ic.deleted_at')
            ->when(
                $pastaId === null,
                fn ($q) => $q->whereNull('ic.pertence'),
                fn ($q) => $q->where('ic.pertence', $pastaId)
            )
            ->orderBy('ic.label')
            ->select(['ic.id', 'ic.cloud_id', 'ic.label', 'ic.pertence', 'ic.created_at', 'ic.updated_at'])
            ->get();

        $cloudPublicId = ApiExternaId::encode($cloudId);
        $ids = $rows->pluck('id')->map(fn ($id) => (int) $id)->all();
        $contagens = $ids === []
            ? collect()
            : DB::table('itens_cloud')
                ->where('cloud_id', $cloudId)
                ->whereIn('pertence', $ids)
                ->whereNull('deleted_at')
                ->selectRaw('pertence, tipo, COUNT(*) as total')
                ->groupBy('pertence', 'tipo')
                ->get()
                ->groupBy('pertence');

        return $rows->map(function ($p) use ($cloudPublicId, $contagens) {
            $itemPublicId = ApiExternaId::encode((int) $p->id);
            $porTipo = ($contagens->get($p->id) ?? collect())->keyBy('tipo');

            return [
                'public_id' => $itemPublicId,
                'cloud_public_id' => $cloudPublicId,
                'label' => $p->label,
                'slug' => Cloud::slugify((string) $p->label),
                'tipo' => 'pasta',
                'pertence_public_id' => ApiExternaId::encode($p->pertence),
                'filhos_pastas_count' => (int) ($porTipo->get('pasta')->total ?? 0),
                'filhos_arquivos_count' => (int) ($porTipo->get('arquivo')->total ?? 0),
                'links' => [
                    'self' => url('/api/v3/cloud/' . $cloudPublicId . '/itens/' . $itemPublicId),
                    'children' => url('/api/v3/cloud/' . $cloudPublicId . '/itens') . '?pasta_public_id=' . $itemPublicId,
                    'pastas' => url('/api/v3/cloud/' . $cloudPublicId . '/pastas') . '?pasta_public_id=' . $itemPublicId,
                ],
                'created_at' => $p->created_at,
                'updated_at' => $p->updated_at,
            ];
        })->values()->all();
    }

    /**
     * Resolve path estilo "documentos/rh/2024" (slugs) → pasta + breadcrumb.
     *
     * @return array{pasta_public_id:?string,caminho:list<array<string,mixed>>,path:string}|null
     */
    public function resolverPath(int $empresaId, int $cloudId, string $path): ?array
    {
        if (! $this->existeCloud($empresaId, $cloudId)) {
            return null;
        }

        $path = trim($path, '/');
        if ($path === '') {
            return [
                'pasta_public_id' => null,
                'caminho' => [],
                'path' => '',
            ];
        }

        $segments = array_values(array_filter(explode('/', $path), fn ($s) => $s !== ''));
        $pertence = null;
        $caminho = [];

        foreach ($segments as $segment) {
            $candidatas = DB::table('itens_cloud')
                ->where('cloud_id', $cloudId)
                ->where('tipo', 'pasta')
                ->whereNull('deleted_at')
                ->when(
                    $pertence === null,
                    fn ($q) => $q->whereNull('pertence'),
                    fn ($q) => $q->where('pertence', $pertence)
                )
                ->select(['id', 'label', 'pertence'])
                ->get();

            $pasta = $candidatas->first(
                fn ($item) => Cloud::slugify((string) $item->label) === $segment
            );

            if (! $pasta) {
                return null;
            }

            $caminho[] = [
                'public_id' => ApiExternaId::encode((int) $pasta->id),
                'label' => $pasta->label,
                'slug' => Cloud::slugify((string) $pasta->label),
            ];
            $pertence = (int) $pasta->id;
        }

        return [
            'pasta_public_id' => ApiExternaId::encode($pertence),
            'caminho' => $caminho,
            'path' => collect($caminho)->pluck('slug')->implode('/'),
            'links' => [
                'itens' => url('/api/v3/cloud/' . ApiExternaId::encode($cloudId) . '/itens')
                    . '?pasta_public_id=' . ApiExternaId::encode($pertence),
            ],
        ];
    }

    /**
     * Resolve o arquivo físico de um item do Cloud (escopo empresa).
     *
     * @return object{id:int,file:?string,thumb:?string,imagem:bool,nome:?string,extensao:?string,disco:?string}|null
     */
    public function resolverArquivoDoItem(int $empresaId, int $cloudId, int $itemId): ?object
    {
        if (! $this->existeCloud($empresaId, $cloudId)) {
            return null;
        }

        $item = DB::table('itens_cloud')
            ->where('cloud_id', $cloudId)
            ->where('id', $itemId)
            ->whereNull('deleted_at')
            ->whereNotNull('arquivo_id')
            ->first(['id', 'arquivo_id', 'tipo']);

        if (! $item || ($item->tipo ?? null) === 'pasta') {
            return null;
        }

        $cols = ['id', 'nome', 'extensao', 'file', 'thumb', 'disco'];
        if (Schema::hasColumn('arquivos', 'imagem')) {
            $cols[] = 'imagem';
        }

        $arquivo = DB::table('arquivos')
            ->where('id', $item->arquivo_id)
            ->select($cols)
            ->first();

        if (! $arquivo) {
            return null;
        }

        $arquivo->imagem = (bool) ($arquivo->imagem ?? false);

        return $arquivo;
    }

    private function existeCloud(int $empresaId, int $cloudId): bool
    {
        return DB::table('clouds')
            ->where('empresa_id', $empresaId)
            ->where('id', $cloudId)
            ->exists();
    }

    private function pastaExiste(int $cloudId, int $pastaId): bool
    {
        return DB::table('itens_cloud')
            ->where('cloud_id', $cloudId)
            ->where('id', $pastaId)
            ->where('tipo', 'pasta')
            ->whereNull('deleted_at')
            ->exists();
    }

    /**
     * IDs de pastas descendentes (BFS), para listagem recursiva.
     *
     * @return Collection<int, int>
     */
    private function idsDescendentes(int $cloudId, int $pastaId): Collection
    {
        $todos = collect();
        $fronteira = [$pastaId];

        while ($fronteira !== []) {
            $filhos = DB::table('itens_cloud')
                ->where('cloud_id', $cloudId)
                ->where('tipo', 'pasta')
                ->whereNull('deleted_at')
                ->whereIn('pertence', $fronteira)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $novos = array_values(array_diff($filhos, $todos->all()));
            if ($novos === []) {
                break;
            }

            $todos = $todos->merge($novos)->unique()->values();
            $fronteira = $novos;
        }

        return $todos;
    }

    /**
     * @return list<array{public_id:string,label:string,slug:string}>
     */
    private function montarCaminho(int $cloudId, int $itemId): array
    {
        $atual = DB::table('itens_cloud')
            ->where('cloud_id', $cloudId)
            ->where('id', $itemId)
            ->whereNull('deleted_at')
            ->first(['id', 'label', 'pertence', 'tipo']);

        if (! $atual) {
            return [];
        }

        $cadeia = [];
        // Inclui o próprio item se for pasta; se arquivo, sobe a partir do pai.
        $cursorId = ($atual->tipo ?? null) === 'pasta'
            ? (int) $atual->id
            : ($atual->pertence ? (int) $atual->pertence : null);

        $guard = 0;
        while ($cursorId && $guard < 64) {
            $pasta = DB::table('itens_cloud')
                ->where('cloud_id', $cloudId)
                ->where('id', $cursorId)
                ->whereNull('deleted_at')
                ->first(['id', 'label', 'pertence']);

            if (! $pasta) {
                break;
            }

            array_unshift($cadeia, [
                'public_id' => ApiExternaId::encode((int) $pasta->id),
                'label' => $pasta->label,
                'slug' => Cloud::slugify((string) $pasta->label),
            ]);

            $cursorId = $pasta->pertence ? (int) $pasta->pertence : null;
            $guard++;
        }

        return $cadeia;
    }

    /**
     * @param  Collection<int, object>  $items
     * @return Collection<int, array<string, mixed>>
     */
    private function mapearColecaoItens(Collection $items, int $cloudId): Collection
    {
        if ($items->isEmpty()) {
            return collect();
        }

        $userIds = $items->flatMap(fn ($r) => [
            $r->quem_criou,
            $r->quem_editou,
            $r->quem_aprovou,
            $r->quem_revisou,
            $r->quem_moveu,
            $r->quem_excluiu,
        ])->filter()->unique()->values()->all();

        $arquivoIds = $items->pluck('arquivo_id')->filter()->unique()->values()->all();
        $pastaIds = $items->pluck('pertence')->merge($items->pluck('pertence_anterior'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $usuarios = $this->carregarUsuariosPorId($userIds);
        $arquivos = $this->carregarArquivosMeta($arquivoIds);
        $pastas = $this->carregarPastasResumo($cloudId, $pastaIds);
        $permissoes = $this->carregarPermissoesGrupos(
            $items->pluck('id')->map(fn ($id) => (int) $id)->all()
        );

        return $items->map(
            fn ($row) => $this->mapItem($row, $usuarios, $arquivos, $pastas, $permissoes)
        )->values();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function carregarMembros(int $empresaId, int $cloudId): array
    {
        if (! Schema::hasTable('user_clouds')) {
            return [];
        }

        $adminGrupoId = DB::table('grupo_clouds')
            ->where('empresa_id', $empresaId)
            ->where('nome', GrupoCloud::NOME_ADMINISTRADORES)
            ->value('id');

        $rows = DB::table('user_clouds as uc')
            ->join('users as u', 'u.id', '=', 'uc.user_id')
            ->leftJoin('grupo_clouds as g', 'g.id', '=', 'u.grupo_cloud_id')
            ->where('uc.cloud_id', $cloudId)
            ->when(Schema::hasColumn('users', 'deleted_at'), fn ($q) => $q->whereNull('u.deleted_at'))
            ->orderBy('u.nome')
            ->select([
                'u.id',
                'u.nome',
                'u.grupo_cloud_id',
                'g.nome as grupo_nome',
            ])
            ->get();

        return $rows->map(function ($u) use ($adminGrupoId) {
            $ehAdmin = $adminGrupoId && (int) $u->grupo_cloud_id === (int) $adminGrupoId;

            return [
                'public_id' => ApiExternaId::encode((int) $u->id),
                'nome' => $u->nome,
                'administrador' => (bool) $ehAdmin,
                'grupo_public_id' => ApiExternaId::encode($u->grupo_cloud_id),
                'grupo_nome' => $ehAdmin
                    ? GrupoCloud::NOME_ADMINISTRADORES
                    : ($u->grupo_nome ?: '—'),
            ];
        })->values()->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function carregarGruposEmpresa(int $empresaId): array
    {
        if (! Schema::hasTable('grupo_clouds')) {
            return [];
        }

        return DB::table('grupo_clouds as g')
            ->where('g.empresa_id', $empresaId)
            ->where('g.ativo', true)
            ->where('g.nome', '!=', GrupoCloud::NOME_ADMINISTRADORES)
            ->orderBy('g.nome')
            ->select(['g.id', 'g.nome', 'g.descricao'])
            ->selectSub(
                DB::table('users as u')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('u.grupo_cloud_id', 'g.id')
                    ->where('u.ativo', true)
                    ->when(Schema::hasColumn('users', 'deleted_at'), fn ($q) => $q->whereNull('u.deleted_at')),
                'usuarios_count'
            )
            ->get()
            ->map(fn ($g) => [
                'public_id' => ApiExternaId::encode((int) $g->id),
                'nome' => $g->nome,
                'descricao' => $g->descricao,
                'usuarios_count' => (int) ($g->usuarios_count ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<int>  $ids
     */
    private function carregarUsuariosPorId(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return DB::table('users')
            ->whereIn('id', $ids)
            ->select(['id', 'nome'])
            ->get()
            ->keyBy('id');
    }

    /**
     * @param  list<int>  $ids
     */
    private function carregarArquivosMeta(array $ids): Collection
    {
        if ($ids === [] || ! Schema::hasTable('arquivos')) {
            return collect();
        }

        $cols = ['id', 'nome', 'extensao', 'bytes', 'created_at', 'updated_at'];
        if (Schema::hasColumn('arquivos', 'imagem')) {
            $cols[] = 'imagem';
        }

        return DB::table('arquivos')
            ->whereIn('id', $ids)
            ->select($cols)
            ->get()
            ->keyBy('id');
    }

    /**
     * @param  list<int>  $ids
     */
    private function carregarPastasResumo(int $cloudId, array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return DB::table('itens_cloud')
            ->where('cloud_id', $cloudId)
            ->whereIn('id', $ids)
            ->whereNull('deleted_at')
            ->select(['id', 'label', 'tipo', 'pertence'])
            ->get()
            ->keyBy('id')
            ->map(fn ($p) => [
                'public_id' => ApiExternaId::encode((int) $p->id),
                'label' => $p->label,
                'tipo' => $p->tipo,
                'pertence_public_id' => ApiExternaId::encode($p->pertence),
            ]);
    }

    /**
     * @param  list<int>  $itemIds
     * @return Collection<int, list<array<string, mixed>>>
     */
    private function carregarPermissoesGrupos(array $itemIds): Collection
    {
        if ($itemIds === [] || ! Schema::hasTable('permissoes_itens_clouds')) {
            return collect();
        }

        $rows = DB::table('permissoes_itens_clouds as p')
            ->join('grupo_clouds as g', 'g.id', '=', 'p.grupo_cloud_id')
            ->whereIn('p.item_id', $itemIds)
            ->select(['p.item_id', 'g.id', 'g.nome'])
            ->orderBy('g.nome')
            ->get();

        return $rows->groupBy('item_id')->map(function (Collection $group) {
            return $group->map(fn ($g) => [
                'public_id' => ApiExternaId::encode((int) $g->id),
                'nome' => $g->nome,
            ])->values()->all();
        });
    }

    private function mapItem(
        object $row,
        Collection $usuarios,
        Collection $arquivos,
        Collection $pastas,
        Collection $permissoes
    ): array {
        $mapUser = function ($id) use ($usuarios) {
            if (! $id) {
                return null;
            }
            $u = $usuarios->get((int) $id);

            return $u ? [
                'public_id' => ApiExternaId::encode((int) $u->id),
                'nome' => $u->nome,
            ] : [
                'public_id' => ApiExternaId::encode((int) $id),
                'nome' => null,
            ];
        };

        $cloudPublicId = ApiExternaId::encode((int) $row->cloud_id);
        $itemPublicId = ApiExternaId::encode((int) $row->id);
        $links = $this->montarLinksItem($cloudPublicId, $itemPublicId, (string) ($row->tipo ?? ''));
        $arquivoRow = $row->arquivo_id ? $arquivos->get((int) $row->arquivo_id) : null;
        $arquivo = $arquivoRow
            ? $this->mapArquivoComLinks($arquivoRow, $links)
            : null;

        return [
            'public_id' => $itemPublicId,
            'cloud_public_id' => $cloudPublicId,
            'arquivo_public_id' => ApiExternaId::encode($row->arquivo_id),
            'label' => $row->label,
            'slug' => Cloud::slugify((string) $row->label),
            'tipo' => $row->tipo,
            'pertence_public_id' => ApiExternaId::encode($row->pertence),
            'pertence' => $row->pertence ? ($pastas->get((int) $row->pertence) ?? null) : null,
            'pertence_anterior_public_id' => ApiExternaId::encode($row->pertence_anterior),
            'arquivo' => $arquivo,
            'links' => $links,
            'criou' => $mapUser($row->quem_criou),
            'editou' => $mapUser($row->quem_editou),
            'aprovou' => $mapUser($row->quem_aprovou),
            'revisou' => $mapUser($row->quem_revisou),
            'moveu' => $mapUser($row->quem_moveu),
            'excluiu' => $mapUser($row->quem_excluiu),
            'aprovado' => (bool) $row->aprovado,
            'data_aprovacao' => $row->data_aprovacao,
            'revisado' => (bool) $row->revisado,
            'data_revisao' => $row->data_revisao,
            'movido' => (bool) $row->movido,
            'data_movido' => $row->data_movido,
            'permissoes' => $permissoes->get((int) $row->id) ?? [],
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function montarLinksItem(?string $cloudPublicId, ?string $itemPublicId, string $tipo): array
    {
        if (! $cloudPublicId || ! $itemPublicId) {
            return [];
        }

        $base = url('/api/v3/cloud/' . $cloudPublicId . '/itens/' . $itemPublicId);

        if ($tipo === 'pasta') {
            return [
                'self' => $base,
                'children' => url('/api/v3/cloud/' . $cloudPublicId . '/itens') . '?pasta_public_id=' . $itemPublicId,
                'pastas' => url('/api/v3/cloud/' . $cloudPublicId . '/pastas') . '?pasta_public_id=' . $itemPublicId,
            ];
        }

        return [
            'self' => $base,
            'url' => $base . '/arquivo',
            'url_download' => $base . '/download',
            'url_thumb' => $base . '/thumb',
            'urlDownload' => $base . '/download',
            'urlThumb' => $base . '/thumb',
        ];
    }

    /**
     * @param  array<string, string>  $links
     * @return array<string, mixed>
     */
    private function mapArquivoComLinks(object $a, array $links): array
    {
        return [
            'public_id' => ApiExternaId::encode((int) $a->id),
            'nome' => $a->nome,
            'extensao' => $a->extensao,
            'bytes' => $a->bytes,
            'imagem' => (bool) ($a->imagem ?? false),
            'url' => $links['url'] ?? null,
            'url_download' => $links['url_download'] ?? null,
            'url_thumb' => $links['url_thumb'] ?? null,
            'urlDownload' => $links['urlDownload'] ?? null,
            'urlThumb' => $links['urlThumb'] ?? null,
            'created_at' => $a->created_at,
            'updated_at' => $a->updated_at,
        ];
    }
}
