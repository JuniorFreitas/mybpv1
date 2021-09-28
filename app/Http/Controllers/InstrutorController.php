<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Instrutor;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class InstrutorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.cadastros.instrutor.index');
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
        $this->authorize('cadastro_instrutor_insert');
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados,
            [
                'nome' => 'required',
                'cargo' => 'required',
                'registro' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar Instrutor',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $resposta = Instrutor::create($dados);

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


                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE INSTRUTOR:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
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
     * @return Instrutor|\Illuminate\Database\Eloquent\Builder|\Illuminate\Http\Response
     */
    public function edit($id)
    {
        return Instrutor::where('id', $id)->with('Anexos')->first();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->authorize('cadastro_instrutor_update');
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados,
            [
                'nome' => 'required',
                'cargo' => 'required',
                'registro' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Atualizar Instrutor',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $instrutor = Instrutor::where('id', $id)->first();

                $instrutor->update($dados);

//                // Excluir um Arquivo, falta a parte de deletar da tabela pivot
//                if (isset($dados['anexosDel'])) {
//                    foreach ($dados['anexosDel'] as $id_anexo) {
//                        $arquivo = Arquivo::find($id_anexo);
//                        $arquivo->excluir();
//                    }
//                }

                if (isset($dados['anexos'])) {
                    foreach ($dados['anexos'] as $index => $anexo) {
//                    dd($anexo);
                        //Se nao tem chave, entao é uma foto que já estava cadastrada no banco
                        if ($anexo['chave'] == null) {
                            Arquivo::whereId($anexo['id'])->update([
                                'nome' => $anexo['nome'],
                            ]);
                            $instrutor->Anexos()->updateExistingPivot($anexo['id'], ['ordem' => $index]);
                        } else {
                            $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                            if ($arquivo) {
                                $arquivo->temporario = false;
                                $arquivo->chave = '';
                                $arquivo->save();
                                $instrutor->Anexos()->attach($arquivo->id);
                            }
                        }
                    }
                }


                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error UPDATE INSTRUTOR:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
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


    public function atualizar(Request $request)
    {
        $resultado = Instrutor::whereNotNull('nome');

        if ($request->filled('campoBusca')) {
            $resultado->where('nome', 'like', '%' . $request->campoBusca . '%');
        }

        $resultado = $resultado->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' =>
                [
                    'items' => $resultado->items()
                ]
        ]);
    }


    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        if ($request->file('arquivo')->isValid()) {
            $mimeType = $request->file('arquivo')->getMimeType();
            $permitidos = [
                Arquivo::MIME_JPEG,
                Arquivo::MIME_PNG,
                Arquivo::MIME_PDF,
                Arquivo::MIME_JPG,
                Arquivo::MIME_GIF,
            ];
            if (in_array($mimeType, $permitidos)) {
                $arquivo = Arquivo::gravaArquivo($request, 'arquivo', 'disco-ocorrencia');
                return response()->json($arquivo, 201);
            } else {
                return response()->json([
                    'msg' => "O upload do arquivo \"{$request->file('arquivo')->getClientOriginalName()}\" falhou. Permitidos apenas imagens JPG/JPEG ou PDF.",
                    'erros' => []
                ], 400);
            }
        } else {
            return response()->json([
                'msg' => "O upload do anexo falhou",
                'erros' => []
            ], 400);
        }
    }

    public function anexoShow(Request $request, $arquivo)
    {
        $path = Arquivo::buscaPath($arquivo);
        if ($path == false) {
            return response("", 404);
        } else {
            $conteudo = Arquivo::buscaConteudo($arquivo);
            header("Content-type: " . Arquivo::getMimeType($path));
            header('Content-Length: ' . filesize($path));
            echo $conteudo;
        }
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        //Se esta apagando realmente um anexo_imovel
        $disco = Arquivo::nomeDisco($arquivo);
        $permitidos = [
            Arquivo::DISCO_OCORRENCIA
        ];
        if (in_array($disco, $permitidos) == false) {
            return response("", 404);
        }
        //Apagar
        $model = Arquivo::findByArquivo($arquivo);
        if ($model && $model->temporario) {
            Arquivo::apagar($arquivo);
            return response("", 200);

        } else {
            return response("Não foi possível apagar o anexo", 400);
        }

    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        //Fazer a validacao (middleware) de download para anexos-cliente , anexos-ocorrencias, aqui se nescessario...
        $disco = Arquivo::nomeDisco($arquivo);
        $permitidos = [
            Arquivo::DISCO_OCORRENCIA
        ];
        if (in_array($disco, $permitidos) == false) {
            return response("", 404);
        }

        $url = Arquivo::buscaPath($arquivo);
        if ($url) {
            $model = Arquivo::findByArquivo($arquivo);
            return response()->download($url, $model->nome . $model->extensao);
        } else {
            return response("", 404);
        }
    }

}
