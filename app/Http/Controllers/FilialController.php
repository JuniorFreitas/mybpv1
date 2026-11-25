<?php

namespace App\Http\Controllers;


use App\Http\Requests\Cadastros\StoreFilialRequest;
use App\Http\Requests\Cadastros\UpdateFilialRequest;
use App\Models\Area;
use App\Models\ClienteFilial;
use App\Models\Servico;
use App\Models\Sistema;
use App\Models\User;
use DB;
use Illuminate\Http\Request;


class FilialController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreFilialRequest $request)
    {
        $dados = $request->all();

        $dto = ClienteFilial::DTO();

        $dados = array_fill_keys((array)$dto, $dados)[""];
        unset($dados['id']);

        try {
            DB::beginTransaction();
            ClienteFilial::withoutGlobalScopes()->create($dados);
            DB::commit();
            return response()->json([], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "error STORE Filial:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json([
                'msg' => $msg,
            ], 400);
        }
    }

    public function edit(ClienteFilial $filial)
    {
        return $filial;
    }

    /**
     * @param Request $request
     * @param ClienteFilial $filial
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateFilialRequest $request, ClienteFilial $filial)
    {
        $dados = $request->all();

        $dto = ClienteFilial::DTO();

        $dados = array_fill_keys((array)$dto, $dados)[""];

        try {
            DB::beginTransaction();
            $filial->update($dados);
            DB::commit();
            return response()->json([], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "error Update Filial:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json([
                'msg' => $msg,
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Cliente $clientes
     * @return \Illuminate\Http\Response
     */

    public function destroy(ClienteFilial $filial)
    {
        $this->authorize('administracao_clientes_delete');
        $filial->delete();
    }

    public function atualizar(Request $request)
    {
        $resultado = ClienteFilial::whereEmpresaId($request->empresa_id)->orderBy('id')->paginate(50);
        $servicos = Servico::whereAtivo(true)->orderBy('titulo')->get();
        $areas = Area::whereAtivo(true)->get();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'dto' => ClienteFilial::DTO(),
                'servicos' => $servicos,
                'areas' => $areas,
            ]
        ]);
    }

    public function ativaDesativa(ClienteFilial $filial)
    {
        $filial->ativo = !$filial->ativo;
        $filial->save();
        $filial->refresh();

        return response()->json(['ativo' => $filial->ativo], 201);
    }

    public function buscaCNPJ(Request $request)
    {
        return Sistema::verificaCnpjCadastrado(ClienteFilial::class, $request->dados->cnpj);
    }

}
