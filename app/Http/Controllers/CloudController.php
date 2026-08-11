<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Cloud;
use App\Models\GrupoCloud;
use App\Models\ItensCloud;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CloudController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.cloud.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('cloud_insert');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'label' => 'required|min:1|unique:itens_cloud,label',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao criar nova pasta',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {

            $dados['quem_criou'] = auth()->id();
            $cloud = ItensCloud::create($dados);

            $dadosPermissao = [];
            if ($request->filled('permissoes')) {
                foreach ($dados['permissoes'] as $grupo) {
                    $dadosPermissao[] = $grupo['id'];
                }
            }
            $permissoes = ItensCloud::permissoesComAdministradores($dadosPermissao);
            // Mantém compatibilidade com permissão financeira legada quando existir
            if (GrupoCloud::query()->whereKey(GrupoCloud::GRUPOADMINFINANCEIRO)->exists()) {
                $permissoes[] = GrupoCloud::GRUPOADMINFINANCEIRO;
                $permissoes = array_values(array_unique($permissoes));
            }

            $cloud->Permissoes()->sync($permissoes);

            return response()->json([], 201);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Cloud $cloud
     * @return \Illuminate\Http\Response
     */
    public function show(Cloud $cloud)
    {
        return abort(403);
    }

    public function editarPasta(ItensCloud $item)
    {
        $this->authorize('cloud');
        Cloud::encontrarAutorizadoOuAbortar($item->cloud_id);

        if (!$item->TemPermissao) {
            return response()->json(['msg' => 'Sem permissão para acessar este item'], 403);
        }

        $iteCloud = $item;
        $iteCloud->permissoes = $item->Permissoes->transform(function ($i) {
            $i->permitido = true;
            return $i;
        });
        return $iteCloud;
    }

    /**
     * @param string $slug
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getSingle(string $slug)
    {
        $this->authorize('cloud');

        $cloud = Cloud::encontrarAutorizadoPorSlugOuAbortar($slug);

        return view('g.cloud.index', compact('cloud'));
    }

    /**
     * Redireciona URLs antigas /cloud/{id}/{titulo} para /cloud/{slug}.
     */
    public function redirectLegacy($id, $titulo = null)
    {
        $this->authorize('cloud');
        $cloud = Cloud::encontrarAutorizadoOuAbortar($id);

        return redirect()->route('g.cloud.cloud.single', ['slug' => $cloud->slug], 301);
    }

    /**
     * Resolve o caminho amigável (?path=pasta/subpasta) para pasta_id + breadcrumb.
     */
    public function resolverPath(Request $request, string $slug)
    {
        $this->authorize('cloud');
        $cloud = Cloud::encontrarAutorizadoPorSlugOuAbortar($slug);

        $path = trim((string) $request->query('path', ''), '/');
        if ($path === '') {
            return response()->json([
                'pasta_id' => '',
                'caminho' => [],
            ]);
        }

        $segments = array_values(array_filter(explode('/', $path), fn ($s) => $s !== ''));
        $pertence = null;
        $caminho = [];

        foreach ($segments as $segment) {
            $query = ItensCloud::query()
                ->where('cloud_id', $cloud->id)
                ->where('tipo', 'pasta');

            if ($pertence === null) {
                $query->whereNull('pertence');
            } else {
                $query->where('pertence', $pertence);
            }

            $pasta = $query->get()->first(function (ItensCloud $item) use ($segment) {
                return Cloud::slugify($item->label) === $segment;
            });

            if (!$pasta) {
                return response()->json(['msg' => 'Caminho não encontrado'], 404);
            }

            if (!$pasta->TemPermissao) {
                return response()->json(['msg' => 'Sem permissão para acessar a pasta'], 403);
            }

            $caminho[] = [
                'id' => $pasta->id,
                'label' => $pasta->label,
                'slug' => Cloud::slugify($pasta->label),
            ];
            $pertence = $pasta->id;
        }

        return response()->json([
            'pasta_id' => $pertence,
            'caminho' => $caminho,
        ]);
    }

    /**
     * @param Request $request
     * @param int|string $cloud
     * @param int|string|null $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function atualizar(Request $request, $cloud, $id = null)
    {
        $this->authorize('cloud');
        $cloudModel = Cloud::encontrarAutorizadoOuAbortar($cloud);

        $resultado = ItensCloud::whereCloudId($cloudModel->id)
            ->with(
                'Pertence:id,pertence',
                'Arquivo',
                'Criou:id,nome',
                'Editou:id,nome'
            );

        if (!$id) {
            $resultado->whereNull('pertence');
        }

        if ($id) {
            $itemBusca = ItensCloud::query()
                ->where('cloud_id', $cloudModel->id)
                ->whereKey($id)
                ->first();

            if (!$itemBusca) {
                return response()->json(['msg' => 'Pasta ou Arquivo não encontrado!'], 404);
            }

            if ($itemBusca->tipo == 'pasta') {
                if ($itemBusca->TemPermissao) {
                    $resultado->wherePertence($id);
                } else {
                    return response()->json(['msg' => 'Sem permissao para acessar a pasta'], 403);
                }
            } else {
                return response()->json(['msg' => 'O item não é uma pasta'], 400);
            }
        }

        $resultado = $resultado->orderBy('tipo')->orderBy('label')->get();

        $resultado->transform(function (ItensCloud $item) {
            $item->append('TemPermissao');
            $item->slug = Cloud::slugify($item->label);
            return $item;
        });

        $grupos = GrupoCloud::whereAtivo(true)->get()->transform(function ($item) {
            $item->permitido = false;
            return $item;
        });

        $habilidades = auth()->user()->GrupoCloud->Habilidades;

        return response()->json([
            'lista' => $resultado,
            'grupos' => $grupos,
            'habilidades' => $habilidades
        ]);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_CLOUD, $arquivo);
    }

    //anexo ou foto
    public function download($arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_CLOUD, $arquivo);
    }

    //CLOUD CADASTRO
    public function indexCadastro()
    {
//        $this->authorize('cloud_cadastro');
        return view('g.cloud.cadastro.index');
    }

    public function listarClouds(Request $request)
    {
//        $this->authorize('cloud_cadastro');
        $resultado = Cloud::query()
            ->withCount('Usuarios')
            ->orderBy('nome');

        if ($request->filled('campoBusca')) {
            $resultado->where('nome', 'like', '%' . $request->campoBusca . '%');
        }

        $porPagina = (int) ($request->input('porPagina') ?: $request->input('pages') ?: 20);
        $resultado = $resultado->paginate($porPagina);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['lista' => $resultado->items()],
        ]);
    }

    public function storeCloud(Request $request)
    {
//        $this->authorize('cloud_cadastro_insert');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'nome' => [
                'required',
                Rule::unique('clouds')->where(function ($query) use ($request) {
                    return $query->whereNome($request->nome)->whereEmpresaId(auth()->user()->empresa_id);
                }),
            ]
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar o Cloud',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $cloud = Cloud::create($dados);

            foreach ($this->grupoAdmin()->pluck('id') as $uadmin) {
                $cloud->Usuarios()->detach($uadmin);
                $cloud->Usuarios()->attach($uadmin);
            }
            DB::commit();
            return response()->json(['id' => $cloud->id, 'slug' => $cloud->slug], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::debug($e->getMessage());
            return response()->json([
                'msg' => 'Erro ao cadastrar o Cloud',
            ], 400);
        }
    }

    public function edit(Request $request, Cloud $cloud)
    {
        $adminIds = $this->grupoAdmin()->pluck('id');
        $userIds = $cloud->Usuarios()->pluck('users.id');

        $usuarios = User::query()
            ->whereIn('id', $userIds)
            ->with('GrupoCloud:id,nome')
            ->orderBy('nome')
            ->get(['id', 'nome', 'grupo_cloud_id'])
            ->map(function ($usuario) use ($adminIds) {
                $ehAdmin = $adminIds->contains($usuario->id);
                return [
                    'id' => $usuario->id,
                    'nome' => $usuario->nome,
                    'administrador' => $ehAdmin,
                    'grupo_nome' => $ehAdmin
                        ? GrupoCloud::NOME_ADMINISTRADORES
                        : ($usuario->GrupoCloud->nome ?? '—'),
                    'novo' => false,
                ];
            })
            ->values();

        $grupos = GrupoCloud::query()
            ->where('ativo', true)
            ->where('empresa_id', auth()->user()->empresa_id)
            ->where('nome', '!=', GrupoCloud::NOME_ADMINISTRADORES)
            ->withCount(['Usuarios' => function ($query) {
                $query->where('ativo', true);
            }])
            ->orderBy('nome')
            ->get(['id', 'nome', 'descricao']);

        return response()->json([
            'id' => $cloud->id,
            'nome' => $cloud->nome,
            'slug' => $cloud->slug,
            'ativo' => $cloud->ativo,
            'usuarios' => $usuarios,
            'grupos' => $grupos,
            'administradores_ids' => $adminIds->values(),
        ]);
    }

    public function usuariosDoGrupo(GrupoCloud $grupocloud)
    {
        if ((int) $grupocloud->empresa_id !== (int) auth()->user()->empresa_id) {
            return response()->json(['msg' => 'Sem permissão'], 403);
        }

        if ($grupocloud->nome === GrupoCloud::NOME_ADMINISTRADORES) {
            return response()->json(['msg' => 'O grupo Administradores já é incluído automaticamente'], 422);
        }

        $adminId = GrupoCloud::idAdministradores(auth()->user()->empresa_id);

        $usuarios = $grupocloud->Usuarios()
            ->where('ativo', true)
            ->select(['id', 'nome', 'grupo_cloud_id'])
            ->orderBy('nome')
            ->get()
            ->map(function ($usuario) use ($adminId, $grupocloud) {
                return [
                    'id' => $usuario->id,
                    'nome' => $usuario->nome,
                    'administrador' => (int) $usuario->grupo_cloud_id === (int) $adminId,
                    'grupo_nome' => $grupocloud->nome,
                    'novo' => true,
                ];
            })
            ->values();

        return response()->json([
            'grupo' => [
                'id' => $grupocloud->id,
                'nome' => $grupocloud->nome,
            ],
            'usuarios' => $usuarios,
        ]);
    }

    protected function grupoAdmin()
    {
        $grupo = GrupoCloud::where('nome', GrupoCloud::NOME_ADMINISTRADORES)
            ->whereEmpresaId(auth()->user()->empresa_id)
            ->with(['Usuarios' => function ($query) {
                $query->select(['id', 'nome', 'grupo_cloud_id'])->where('ativo', true);
            }])
            ->first();

        return $grupo ? $grupo->usuarios : collect();
    }

    public function updateCloud(Request $request, Cloud $cloud)
    {
//        $this->authorize('cloud_cadastro_update');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'nome' => [
                'required',
                Rule::unique('clouds')->ignore($cloud->id)->where(function ($query) use ($request) {
                    return $query->whereNome($request->nome)->whereEmpresaId(auth()->user()->empresa_id);
                }),
            ]
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar o Cloud',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $cloud->update($dados);

            $cloud->Usuarios()->detach();

            $usuariosIds = collect($request->input('usuarios', []))
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            if ($usuariosIds->isNotEmpty()) {
                $cloud->Usuarios()->attach($usuariosIds);
            }

            foreach ($this->grupoAdmin()->pluck('id') as $uadmin) {
                $cloud->Usuarios()->detach($uadmin);
                $cloud->Usuarios()->attach($uadmin);
            }

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::debug($e->getMessage());
            return response()->json([
                'msg' => 'Erro ao atualizar o Cloud',
            ], 400);
        }
    }

    public function ativaDesativa(Cloud $cloud)
    {
//        $this->authorize('cloud_cadastro_update');
        $cloud->ativo = !$cloud->ativo;
        $cloud->save();
        $cloud->refresh();
        return response()->json(['ativo' => $cloud->ativo], 201);
    }
}
