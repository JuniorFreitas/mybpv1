<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Cloud;
use App\Models\GrupoCloud;
use App\Models\ItensCloud;
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

            $permissoes = collect([GrupoCloud::GRUPOADMIN, GrupoCloud::GRUPOADMINFINANCEIRO]);
            if ($request->filled('permissoes')) {
                $dadosPermissao = [];
                foreach ($dados['permissoes'] as $grupo) {
                    $dadosPermissao[] = $grupo['id'];
                }
                $permissoes = $permissoes->concat($dadosPermissao);
            }

            $cloud->Permissoes()->attach($permissoes);

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
        $iteCloud = $item;
        $iteCloud->permissoes = $item->Permissoes->transform(function ($i) {
            $i->permitido = true;
            return $i;
        });
        return $iteCloud;
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getSingle($id)
    {
        $this->authorize('cloud');

        $cloud = Cloud::with('Itens', 'Raiz')->find($id);
        if(!$cloud){
            return abort(404);
        }
        if(!auth()->user()->Clouds()->find($id)){
            return abort(403);
        }
        return view('g.cloud.index', compact('cloud'));
    }

    /**
     * @param Request $request
     * @param $cloud
     * @param $id
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function atualizar(Request $request, $cloud, $id = null)
    {
        if(!auth()->user()->Clouds()->find($cloud)){
            return abort(403);
        }

        $resultado = ItensCloud::whereCloudId($cloud)
            ->with(
                'Pertence:id,pertence',
                'Arquivo:id,bytes,file,extensao,thumb,imagem',
                'Criou:id,nome',
                'Editou:id,nome'
            );

        if (!$id) {
            $resultado->whereNull('pertence');
        }

        if ($id) {
            $itemBusca = ItensCloud::find($id);
            if (!$itemBusca) {
                return response()->json(['msg' => 'Pasta ou Arquivo não encontrado!'], 400);
            }

            if ($itemBusca->tipo == 'pasta') {
                if ($itemBusca->TemPermissao) {
                    $resultado->wherePertence($id);
                } else {
                    return response()->json(['msg' => 'Sem permissao para acessar a pasta',], 403);
                }
            } else {
                return response()->json(['msg' => 'O item não é uma pasta'], 400);
            }
        }

        $resultado = $resultado->orderBy('tipo')->orderBy('label')->get();

        $resultado->transform(function (ItensCloud $item) {
            $item->append('TemPermissao');
            return $item;
        });

        //Permitindo sempre para Grupo Todos
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
        $resultado = Cloud::orderBy('nome');

        if ($request->filled('campoBusca')) {
            $resultado->where('titulo', 'like', '%' . $request->campoBusca . '%');
        }

        $resultado = $resultado->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['lista' => $resultado->items(),]
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
            Cloud::create($dados);
            DB::commit();
            return response()->json([]);
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
        return $cloud->load('Usuarios');
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

            foreach ($request->usuarios as $usuario) {
                $cloud->Usuarios()->attach($usuario['id']);
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
