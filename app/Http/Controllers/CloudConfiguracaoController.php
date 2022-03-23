<?php

namespace App\Http\Controllers;

use App\Models\GrupoCloud;
use App\Models\HabilidadeCloud;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CloudConfiguracaoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('cloud_configuracoes');
        return view('g.cloud.configuracoes.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('cloud_configuracoes_insert');

        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required|min:2|unique:grupo_clouds,nome',
            'descricao' => 'required|min:2',
            'ativo' => 'required|boolean',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao alterar grupo',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                \DB::beginTransaction();
                $grupocloud = GrupoCloud::create($dados);
                $habilidades = collect($request->habilidades)->filter(function ($habilidade) {
                    if ($habilidade['acesso'] == 'true') {
                        return $habilidade;
                    }
                })->pluck('id');
                $grupocloud->habilidades()->sync($habilidades);
                \DB::commit();
                return response()->json([], 201);
            } catch (\ErrorException $e) {
                DB::rollBack();
                return response($e->getMessage(), 400);
            }

        }
    }

    /**
     * Display the specified resource.
     *
     * @param GrupoCloud $grupocloud
     * @return \Illuminate\Http\Response
     */
    public function show(GrupoCloud $grupocloud)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param GrupoCloud $grupocloud
     * @return \Illuminate\Http\Response
     */
    public function edit(GrupoCloud $grupocloud)
    {
        $this->authorize('cloud_configuracoes_update');
        return $grupocloud->load('habilidades', 'Usuarios:id,nome,grupo_cloud_id');

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param GrupoCloud $grupocloud
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GrupoCloud $grupocloud)
    {
        $this->authorize('cloud_configuracoes_update');

        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required|min:2|unique:grupo_clouds,nome,' . $grupocloud->id,
            'descricao' => 'required|min:3',
            'ativo' => 'required|boolean',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao alterar grupo',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                \DB::beginTransaction();
                $grupocloud->update($dados);
                $habilidades = collect($request->habilidades)->filter(function ($habilidade) {
                    if ($habilidade['acesso'] == 'true') {
                        return $habilidade;
                    }
                })->pluck('id');
                $grupocloud->habilidades()->sync($habilidades);

                foreach ($dados['usuariosDelete'] as $id) {
                    User::find($id)->update(['grupo_cloud_id' => null]);
                }

                foreach ($dados['usuarios'] as $linha) {
                    User::find($linha['id'])->update(['grupo_cloud_id' => $grupocloud->id]);
                }

                \DB::commit();
                return response()->json([], 201);
            } catch (\ErrorException $e) {
                DB::rollBack();
                return response($e->getMessage(), 400);
            }

        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param GrupoCloud $grupocloud
     * @return \Illuminate\Http\Response
     */
    public function destroy(GrupoCloud $grupocloud)
    {
        $this->authorize('cloud_configuracoes_delete');
        //
    }

    public function atualizar(Request $request)
    {
        $this->authorize('cloud_configuracoes');
        $resultado = GrupoCloud::with('habilidades')->withCount('Usuarios');

        if ($request->filled('campoBusca')) {
            $resultado->where('titulo', 'like', '%' . $request->campoBusca . '%');
        }

        $resultado = $resultado->paginate($request->pages);

        $listaHabilidades = HabilidadeCloud::get()->transform(function ($item) {
            $item->acesso = false;
            return $item;
        });

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['lista' => $resultado->items(), 'listaHabilidades' => $listaHabilidades]
        ]);
    }
}
