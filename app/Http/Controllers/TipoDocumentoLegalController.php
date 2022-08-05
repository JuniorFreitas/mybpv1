<?php

namespace App\Http\Controllers;

use App\Models\TipoDocumento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TipoDocumentoLegalController extends Controller
{
    public function index(Request $request)
    {
        return view('g.administracao.documentoslegais.tipodocumento.index');

    }

    public function store(Request $request)
    {
        $this->authorize('administracao_documentos_legais_insert');
        $dados = $request->input();
        $arrayValidacao = [
            'nome' => [function ($attribute, $value, $fail) use ($dados) {
                if (strlen($value) <= 3) {
                    $fail('Informe uma nome maior que 3 caracteres.');
                }
            }]
        ];
        $dadosValidados = \Validator::make($dados, $arrayValidacao);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Tipo Documento',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            TipoDocumento::create($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE TIPO DOCUMENTO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function edit(TipoDocumento $tipodocumento)
    {
        return $tipodocumento;
    }

    public function show(TipoDocumento $tipodocumento)
    {
        $this->authorize('administracao_documentos_legais_insert');
        return $tipodocumento;
    }

    public function update(Request $request, TipoDocumento $tipodocumento)
    {
        $dados = $request->input();

        $this->authorize('administracao_documentos_legais_insert');
        $dados = $request->input();
        $arrayValidacao = [
            'nome' => [function ($attribute, $value, $fail) use ($dados) {
                if (strlen($value) <= 3) {
                    $fail('Informe uma nome maior que 3 caracteres.');
                }
            }]
        ];
        $dadosValidados = \Validator::make($dados, $arrayValidacao);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Tipo Documento',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $tipodocumento->update($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE TIPO DOCUMENTO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function atualizar(Request $request)
    {
        $this->authorize('administracao_documentos_legais');
        $porPagina = $request->get('porPagina');
        $resultado = TipoDocumento::orderBy('id');

        if ($request->filled('campoBusca')) {
            $resultado->where('nome', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('tipo', 'like', '%' . $request->campoBusca . '%');
        }

        $resultado = $resultado->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $resultado->items(),
            ]
        ], 200);
    }

    public function ativaDesativa(Request $request)
    {
        $this->authorize('administracao_documentos_legais_insert');

        $tipodocumento = TipoDocumento::find($request->id);
        $tipodocumento->ativo = !$tipodocumento->ativo;
        $tipodocumento->save();
        $tipodocumento->refresh();
        return response()->json(['ativo' => $tipodocumento->ativo], 201);
    }

}
