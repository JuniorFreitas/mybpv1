<?php

namespace App\Http\Controllers;

use App\Http\Requests\Operacional\StoreCloudConfiguracaoRequest;
use App\Http\Requests\Operacional\UpdateCloudConfiguracaoRequest;
use App\Models\Cloud;
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
    public function store(StoreCloudConfiguracaoRequest $request)
    {
        $this->authorize('cloud_configuracoes_insert');

        $dados = $request->validated();

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
    public function update(UpdateCloudConfiguracaoRequest $request, GrupoCloud $grupocloud)
    {
        $this->authorize('cloud_configuracoes_update');

        $dados = $request->validated();
        $dadosCompletos = $request->input();

        try {
            \DB::beginTransaction();

            $membrosAnteriores = $grupocloud->Usuarios()->pluck('id');
            $ehAdministradores = $grupocloud->nome === GrupoCloud::NOME_ADMINISTRADORES;

            $grupocloud->update($dados);
            $habilidades = collect($request->habilidades)->filter(function ($habilidade) {
                if ($habilidade['acesso'] == 'true') {
                    return $habilidade;
                }
            })->pluck('id');
            $grupocloud->habilidades()->sync($habilidades);

            foreach ($dadosCompletos['usuariosDelete'] ?? [] as $id) {
                User::find($id)?->update(['grupo_cloud_id' => null]);
            }

            foreach ($dadosCompletos['usuarios'] ?? [] as $linha) {
                User::find($linha['id'])?->update(['grupo_cloud_id' => $grupocloud->id]);
            }

            if ($ehAdministradores) {
                $membrosAtuais = collect($dadosCompletos['usuarios'] ?? [])->pluck('id');
                $adicionados = $membrosAtuais->diff($membrosAnteriores)->values();
                $removidos = collect($dadosCompletos['usuariosDelete'] ?? [])
                    ->merge($membrosAnteriores->diff($membrosAtuais))
                    ->unique()
                    ->values();

                Cloud::sincronizarMembrosAdministradores(
                    (int) ($grupocloud->empresa_id ?? auth()->user()->empresa_id),
                    $adicionados,
                    $removidos
                );
            }

            \DB::commit();
            return response()->json([], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::debug($e->getMessage());
            return response()->json(['msg' => 'Erro ao alterar grupo'], 400);
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
