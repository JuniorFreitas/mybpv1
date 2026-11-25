<?php

namespace App\Http\Controllers;

use App\Http\Requests\Documentos\StoreServicoDocumentoLegalRequest;
use App\Http\Requests\Documentos\UpdateServicoDocumentoLegalRequest;
use App\Models\Servico;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicoDocumentoLegalController extends Controller
{
    public function index(Request $request)
    {
        return view('g.administracao.documentoslegais.tiposervico.index');

    }

    public function store(StoreServicoDocumentoLegalRequest $request)
    {
        $this->authorize('administracao_documentos_legais_insert');
        $dados = $request->all();

        try {
            DB::beginTransaction();

            Servico::create($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE TIPO SERVICO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function edit(Servico $tiposervico)
    {
        return $tiposervico;
    }

    public function show(Servico $tiposervico)
    {
        $this->authorize('administracao_documentos_legais_insert');
        return $tiposervico;
    }

    public function update(UpdateServicoDocumentoLegalRequest $request, Servico $tiposervico)
    {
        $this->authorize('administracao_documentos_legais_insert');
        $dados = $request->all();

        try {
            DB::beginTransaction();

            $tiposervico->update($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE TIPO SERVICO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function atualizar(Request $request)
    {
        $this->authorize('administracao_documentos_legais');
        $porPagina = $request->get('porPagina');
        $resultado = Servico::orderBy('titulo');

        if ($request->filled('campoBusca')) {
            $resultado->where('titulo', 'like', '%' . $request->campoBusca . '%');
        }

        $permissoes = [
            'insert' => auth()->user()->can('administracao_documentos_legais_tipos_servicos_insert'),
            'update' => auth()->user()->can('administracao_documentos_legais_tipos_servicos_update'),
            'delete' => auth()->user()->can('administracao_documentos_legais_tipos_servicos_delete')
        ];

        $resultado = $resultado->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $resultado->items(),
                'permissoes' => $permissoes
            ]
        ], 200);
    }

    public function ativaDesativa(Request $request)
    {
        $this->authorize('administracao_documentos_legais_insert');

        $tiposervico = Servico::find($request->id);
        $tiposervico->ativo = !$tiposervico->ativo;
        $tiposervico->save();
        $tiposervico->refresh();
        return response()->json(['ativo' => $tiposervico->ativo], 201);
    }

}
