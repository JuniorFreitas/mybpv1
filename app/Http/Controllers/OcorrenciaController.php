<?php

namespace App\Http\Controllers;

use App\Jobs\Ocorrencias\JobOcorrenciaFinaliza;
use App\Jobs\Ocorrencias\JobOcorrenciaNovaMensagem;
use App\Jobs\Ocorrencias\JobOcorrenciaStore;
use App\Models\Arquivo;
use App\Models\Ocorrencia;
use App\Models\OcorrenciaSetor;
use App\Models\RespostaOcorrencia;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use MasterTag\DataHora;
use mysql_xdevapi\Exception;

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
                'setor_id' => ['required', function ($attribute, $value, $fail) {
                    $setor = OcorrenciaSetor::whereId($value)->whereEmpresaId(auth()->user()->empresa_id)->count();
                    if ($setor == 0) {
                        $fail('Setor não cadastrado.');
                    }
                }],
                'tipo' => 'required',
                'resposta' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
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

            $userPara = User::find($dados['usuario_id']);
            JobOcorrenciaStore::dispatch($ocorrencia, $userPara);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE OCORRÊNCIA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
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
        }
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

            $ocorrencia = Ocorrencia::find($dados['ocorrencia_id']);
            $IDCRIADOROcorrencia = $ocorrencia->quem_criou;
            $IDMENCIONADOOcorrencia = $ocorrencia->usuario_id;

            $usuarioRespostaId = $resposta->user_id;

            if ($usuarioRespostaId != $IDCRIADOROcorrencia) {
                $userPara = User::find($IDCRIADOROcorrencia);
            }
            if ($usuarioRespostaId != $IDMENCIONADOOcorrencia) {
                $userPara = User::find($IDMENCIONADOOcorrencia);
            }
            $ocorrencia->resposta_user = User::find($resposta->user_id)->nome;
            JobOcorrenciaNovaMensagem::dispatch($ocorrencia, $userPara);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error NOVA MENSAGEM EM OCORRÊNCIA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function mudarSetor(Request $request)
    {
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados,
            [
                'setor_id' => ['required', function ($attribute, $value, $fail) {
                    $setor = OcorrenciaSetor::whereId($value)->whereEmpresaId(auth()->user()->empresa_id)->count();
                    if ($setor == 0) {
                        $fail('Setor não cadastrado.');
                    }
                }]
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Mudar Setor',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            DB::beginTransaction();
            Ocorrencia::find($dados['ocorrencia_id'])->update([
                'setor_id' => $dados['setor_id']
            ]);
            DB::commit();
            return response()->json([], 201);
        } catch (Exception  $e) {
            DB::rollback();
            $msg = "error mudar Setor OCORRÊNCIA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function finalizar(Request $request)
    {
        $dados = $request->input();
        try {
            DB::beginTransaction();
            Ocorrencia::find($dados['ocorrencia_id'])->update([
                'status' => 'finalizado',
                'quem_finalizou' => auth()->id(),
                'datahora_finalizou' => (new DataHora())->dataHoraInsert(),
            ]);
            DB::commit();

            $ocorrencia = Ocorrencia::find($dados['ocorrencia_id']);

            $IDCRIADOROcorrencia = $ocorrencia->quem_criou;
            $IDMENCIONADOOcorrencia = $ocorrencia->usuario_id;

            $ID_quem_finalizou = $ocorrencia->quem_finalizou;

            if ($ID_quem_finalizou != $IDCRIADOROcorrencia) {
                $userPara = User::find($IDCRIADOROcorrencia);
            }
            if ($ID_quem_finalizou != $IDMENCIONADOOcorrencia) {
                $userPara = User::find($IDMENCIONADOOcorrencia);
            }

            JobOcorrenciaFinaliza::dispatch($ocorrencia, $userPara);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error Finalizar OCORRÊNCIA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

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
        $setores = OcorrenciaSetor::orderBy('nome')->get();
        $tags = Tag::orderBy('nome')->get();
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
        $regra = Rule::unique('tags')->where(function ($query) use ($dados) {
            return $query->whereEmpresaId(auth()->user()->empresa_id)
                ->whereNome($dados['nome']);
        });
        $dadosValidados = \Validator::make($dados,
            [
                'nome' => ['required', $regra],
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            DB::beginTransaction();
            Tag::create($dados);
            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error cadastrar TAG OCORRÊNCIA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function cadastroSetor(Request $request)
    {
        $this->authorize('ocorrencia');
        $dados['nome'] = $request->input('nome');
        $regra = Rule::unique('ocorrencias_setores')->where(function ($query) use ($dados) {
            return $query->whereEmpresaId(auth()->user()->empresa_id)
                ->whereNome($dados['nome']);
        });
        $dadosValidados = \Validator::make($dados,
            [
                'nome' => ['required', $regra],
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            DB::beginTransaction();
            OcorrenciaSetor::create($dados);
            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error cadastrar Setor OCORRÊNCIA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

}
