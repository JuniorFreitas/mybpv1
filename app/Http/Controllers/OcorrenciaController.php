<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Ocorrencia;
use App\Models\OcorrenciaSetor;
use App\Models\RespostaOcorrencia;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class OcorrenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('ocorrencia');
        return view('g.ocorrencia.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('ocorrencia');
        $dados = $request->input();
        $dados['cliente_id'] = $dados['tipo_ocorrencia'] == 'cliente' ? $dados['cliente_id'] : null;
        $dados['usuario_id'] = $dados['tipo_ocorrencia'] == 'usuario' ? $dados['usuario_id'] : null;
        $dados['quem_criou'] = auth()->id();
        $dados['quem_atualizou'] = auth()->id();

        // Validação Comum
        $dadosValidados = \Validator::make($dados,
            [
                'assunto' => 'required|min:2',
                'setor_id' => 'required',
                'tipo' => 'required',
                'resposta' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                //Se o tipo for Anotação
                if ($dados['tipo'] == 'anotacao') {
                    $dados['status'] = 'finalizado';
                    $dados['quem_finalizou'] = auth()->id();
                    $dados['datahora_finalizou'] = (new DataHora())->dataHoraInsert();
                } else {
                    $dados['status'] = 'novo';
                }

                $ocorrencia = Ocorrencia::create($dados);
                $dados['ocorrencia_id'] = $ocorrencia->id;
                $dados['user_id'] = auth()->id();

                $dados['resposta'] = html_entity_decode($dados['resposta']);
                $dados['resposta'] = strip_tags($dados['resposta'], "<p><a><strong><i><ul><li><ol><table><tbody><tr><td>"); // permitir apenas essas tags
                $resposta = RespostaOcorrencia::create($dados);
                $dados['resposta_id'] = $resposta->id;

                $ocorrencia->Tags()->attach($dados['tag_id']);

                if ($request->filled('anexos')) {
                    foreach ($dados['anexos'] as $item) {
                        $resposta->Anexos()->attach($item['id']);
                        $resposta->Anexos()->where('id', $item['id'])
                            ->where('temporario', true)
                            ->where('chave', $item['chave'])
                            ->update([
                                'temporario' => false,
                                'chave' => '',
                                'nome' => $item['nome']
                            ]); // tira dos temporarioorarios

                    }
                }
                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE OCORRÊNCIA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }

    }

    public function novaMensagem(Request $request)
    {
        $dados = $request->input();
        $dados['user_id'] = auth()->id();
        $dadosValidados = \Validator::make($dados, ['resposta' => 'required']);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                Ocorrencia::find($dados['ocorrencia_id'])->update([
                    'quem_atualizou' => auth()->id(),
                    'status' => 'andamento',
                ]);

                $dados['resposta'] = html_entity_decode($dados['resposta']);
                $dados['resposta'] = strip_tags($dados['resposta'], "<p><a><strong><i><ul><li><ol><table><tbody><tr><td>"); // permitir apenas essas tags

                $resposta = RespostaOcorrencia::create($dados);
                if ($request->filled('anexos')) {
                    foreach ($dados['anexos'] as $item) {
                        $resposta->Anexos()->attach($item['id']);
                        $resposta->Anexos()->where('id', $item['id'])
                            ->where('temporario', true)
                            ->where('chave', $item['chave'])
                            ->update([
                                'temporario' => false,
                                'chave' => '',
                                'nome' => $item['nome']
                            ]); // tira dos temporarioorarios
                    }
                }
                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error NOVA MENSAGEM EM OCORRÊNCIA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    public function mudarSetor(Request $request)
    {
        $dados = $request->input();
        Ocorrencia::find($dados['ocorrencia_id'])->update([
            'setor_id' => $dados['setor_id']
        ]);
        return response()->json([], 201);
    }

    public function finalizar(Request $request)
    {
        $dados = $request->input();
        Ocorrencia::find($dados['ocorrencia_id'])->update([
            'status' => 'finalizado',
            'quem_finalizou' => auth()->id(),
            'datahora_finalizou' => (new DataHora())->dataHoraInsert(),
        ]);
        return response()->json([], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Ocorrencia|\Illuminate\Http\Response
     */
    public function edit(Ocorrencia $ocorrencia)
    {
        return $ocorrencia->load('Respostas', 'Anexos');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function listaSetoresTags(Request $request)
    {
        $setores = OcorrenciaSetor::get();
        $tags = Tag::get();
        return response()->json(['setores' => $setores, 'tags' => $tags], 200);
    }

    public function atualizar(Request $request)
    {

        $porPagina = $request->get('porPagina');
        $resultado = Ocorrencia::with('Criou:id,nome');

        // se tiver busca
        if ($request->filled('campoBusca')) {
            $resultado->where(function ($q) use ($request) {
                $q->where('assunto', 'like', '%' . $request->campoBusca . '%')
                    ->orWhereHas('Respostas', function ($q) use ($request) {
                        $q->where('resposta', 'like', '%' . $request->campoBusca . '%');
                    });
            });
        }
        //Busca por setor
        if ($request->filled('campoSetor')) {
            $resultado->where('setor_id', $request->campoSetor);
        }
        //Busca por tag
        if ($request->filled('campoTag')) {
            $resultado->whereHas('Tags', function ($q) use ($request) {
                $q->whereTagId($request->campoTag);
            });
        }
        // se for um tipo Problema ou Anotação
        if ($request->filled('campoTipo')) {
            $resultado->where('tipo', $request->campoTipo);
        }
        // Se for qualquer tipo diferente de anotação, filtar pelo status
        if ($request->filled('campoStatus') && $request->filled('campoTipo') && $request->input('campoTipo') != Ocorrencia::TIPO_ANOTACAO) {
            $resultado->where('status', $request->campoStatus);
        }

        //filtros...
        if ($request->filled('campoFiltro')) {
            if ($request->input('campoFiltro') == 'imovel') {// se for apenas imóvel
                $resultado->whereNotNull('imovel_id')->whereNull('contrato_id'); // imovel_id com dados, e contrato_id = null
            } else {
                $resultado->whereNotNull('contrato_id')->whereNotNull('imovel_id'); // imovel_id e contrato_id com dados
            }
        }

        $permissoes = auth()->user()->listaDeHabilidades();

        $resultado = $resultado->orderByDesc('updated_at')->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $resultado->items(),
                'permissoes' => $permissoes,
            ]
        ], 200);

    }

    public function Exibir(Request $request)
    {
//        $ocorrencia = Ocorrencia::whereId($request->id)->with('Setor', 'Criou:id,nome', 'Atualizou:id,nome', 'Finalizou:id,nome')->first();
        $ocorrencia = Ocorrencia::whereId($request->id)->with(
            'Setor',
            'Tags',
            'Criou:id,nome',
            'Atualizou:id,nome',
            'Finalizou:id,nome',
            'Respostas',
            'Respostas.Usuario',
            'Respostas.Anexos',
            'Usuario', 'Cliente')
            ->first();

        return response()->json($ocorrencia, 200);
    }

    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::DISCO_OCORRENCIA);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_OCORRENCIA, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_OCORRENCIA, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload([Arquivo::DISCO_CLOUD], $arquivo);
    }

    public function cadastroTag(Request $request)
    {
        $this->authorize('ocorrencia');
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados,
            [
                'nome' => 'required|min:1',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            Tag::create($dados);
            return response()->json([], 201);
        }
    }

    public function cadastroSetor(Request $request)
    {
        $this->authorize('ocorrencia');
        $dados['nome'] = $request->input('nome');
        $dadosValidados = \Validator::make($dados,
            [
                'nome' => 'required|min:1',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            OcorrenciaSetor::create($dados);
            return response()->json([], 201);
        }
    }

}
