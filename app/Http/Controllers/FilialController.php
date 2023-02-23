<?php

namespace App\Http\Controllers;


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
    public function store(Request $request)
    {
        $this->authorize('administracao_clientes_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $dto = ClienteFilial::DTO();

        $dados = array_fill_keys((array)$dto, $dados)[""];
        unset($dados['id']);

        $validaComum = [
            'dados.cnpj' => 'required|min:18',
            'dados.razao_social' => 'required|min:2',
            'dados.nome_fantasia' => 'required|min:2',
            'dados.area_id' => 'required',
            'dados.ramo' => 'required',
            'dados.cep' => 'required|min:9',
            'dados.uf' => 'required|min:2',
            'dados.logradouro' => 'required|min:3',
            'dados.bairro' => 'required|min:3',
            'dados.municipio' => 'required|min:3',
            'dados.email' => 'required|email',
            'dados.telefone' => 'required|min:14',
            'ativo' => 'required',
        ];

        $dadosValidados = \Validator::make($dados, $validaComum);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Filial',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
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
    public function update(Request $request, ClienteFilial $filial)
    {
        $this->authorize('administracao_clientes_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $dto = ClienteFilial::DTO();

        $dados = array_fill_keys((array)$dto, $dados)[""];

        $validaComum = [
            'dados.cnpj' => 'required|min:18',
            'dados.razao_social' => 'required|min:2',
            'dados.nome_fantasia' => 'required|min:2',
            'dados.area_id' => 'required',
            'dados.ramo' => 'required',
            'dados.cep' => 'required|min:9',
            'dados.uf' => 'required|min:2',
            'dados.logradouro' => 'required|min:3',
            'dados.bairro' => 'required|min:3',
            'dados.municipio' => 'required|min:3',
            'dados.email' => 'required|email',
            'dados.telefone' => 'required|min:14',
            'ativo' => 'required',
        ];

        $dadosValidados = \Validator::make($dados, $validaComum);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Filial',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
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
