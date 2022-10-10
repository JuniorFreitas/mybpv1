<?php

namespace App\Http\Controllers;

use App\Models\FormaContrato;
use App\Models\User;
use App\Rules\TenantUniqueRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FormaContratoDocumentoLegalController extends Controller
{
    public function index(Request $request)
    {
        return view('g.administracao.documentoslegais.formacontrato.index');

    }

    public function store(Request $request)
    {
        $this->authorize('administracao_documentos_legais_insert');
        $dados = $request->input();
        $arrayValidacao = [
            'titulo' => [
                'required',
                'min:3',
                new TenantUniqueRules('documentos_legais_forma_contrato', $request->segment(5)),
            ],
        ];
        $dadosValidados = \Validator::make($dados, $arrayValidacao);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Forma Contrato',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            FormaContrato::create($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE FORMA CONTRATO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function edit(FormaContrato $formacontrato)
    {
        return $formacontrato;
    }

    public function show(FormaContrato $formacontrato)
    {
        $this->authorize('administracao_documentos_legais_insert');
        return $formacontrato;
    }

    public function update(Request $request, FormaContrato $formacontrato)
    {
        $dados = $request->input();

        $this->authorize('administracao_documentos_legais_insert');
        $dados = $request->input();
        $arrayValidacao = [
            'titulo' => [
                'required',
                'min:3',
                new TenantUniqueRules('documentos_legais_forma_contrato', $request->segment(5)),
            ],
        ];
        $dadosValidados = \Validator::make($dados, $arrayValidacao);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Forma Contrato',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $formacontrato->update($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE FORMA CONTRATO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function atualizar(Request $request)
    {
        $this->authorize('administracao_documentos_legais');
        $porPagina = $request->get('porPagina');
        $resultado = FormaContrato::orderBy('titulo');

        if ($request->filled('campoBusca')) {
            $resultado->where('titulo', 'like', '%' . $request->campoBusca . '%');
        }

        $permissoes = [
            'insert' => auth()->user()->can('administracao_documentos_legais_formas_contratos_insert'),
            'update' => auth()->user()->can('administracao_documentos_legais_formas_contratos_update'),
            'delete' => auth()->user()->can('administracao_documentos_legais_formas_contratos_delete')
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

        $formacontrato = FormaContrato::find($request->id);
        $formacontrato->ativo = !$formacontrato->ativo;
        $formacontrato->save();
        $formacontrato->refresh();
        return response()->json(['ativo' => $formacontrato->ativo], 201);
    }

}
