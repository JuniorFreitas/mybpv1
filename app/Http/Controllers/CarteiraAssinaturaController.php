<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\CarteiraAssinatura;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class CarteiraAssinaturaController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('cadastro_carteira_assinatura');
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados,
            [
                'nome' => 'required',
                'tipo' => 'required',
            ]
        );
        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao Cadastrar Assinatura Carteira',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $resposta = CarteiraAssinatura::create($dados);

                if (isset($dados['anexos'])) {
                    foreach ($dados['anexos'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $resposta->Anexos()->attach($arquivo->id);
                        }
                    }
                }

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE ASSINATURA CARTEIRA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return CarteiraAssinatura|CarteiraAssinatura[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|\Illuminate\Http\Response
     */
    public function edit($id)
    {
        $assinatura = CarteiraAssinatura::with('Anexos')->find($id);
        $assinatura->anexosDel = [];
        return $assinatura;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->authorize('cadastro_carteira_assinatura');
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados,
            [
                'nome' => 'required',
                'tipo' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar Assinatura Carteira',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $assinatura = CarteiraAssinatura::where('id', $id)->first();
                $assinatura->update($dados);

                if (isset($dados['anexosDel'])) {
                    foreach ($dados['anexosDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                if (isset($dados['anexos'])) {
                    foreach ($dados['anexos'] as $index => $anexo) {
                        if ($anexo['chave'] == null) {
                            Arquivo::whereId($anexo['id'])->update([
                                'nome' => $anexo['nome'],
                            ]);
                            $assinatura->Anexos()->updateExistingPivot($anexo['id'], ['ordem' => $index]);
                        } else {
                            $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                            if ($arquivo) {
                                $arquivo->temporario = false;
                                $arquivo->chave = '';
                                $arquivo->save();
                                $assinatura->Anexos()->attach($arquivo->id);
                            }
                        }
                    }
                }
                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error UPDATE ASSINATURA CARTEIRA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
            }
        }
    }

    public function atualizar(Request $request)
    {
        $this->authorize('cadastro_carteira_assinatura');
        $porPagina = $request->get('porPagina');
        $resultado = CarteiraAssinatura::orderBy('id');

        $resultado = $resultado->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $resultado->items(),
                'lista_tipos_assinatura' => CarteiraAssinatura::TIPOS
            ]
        ], 200);
    }

    public function uploadAnexos(Request $request)
    {
        if ($request->file('arquivo')->isValid()) {
            $mimeType = $request->file('arquivo')->getMimeType();
            $permitidos = [
                Arquivo::MIME_JPEG,
                Arquivo::MIME_PNG,
                Arquivo::MIME_JPG,
                Arquivo::MIME_GIF,
            ];
            if (in_array($mimeType, $permitidos)) {
                $arquivo = Arquivo::gravaArquivo($request, 'arquivo', 'disco-assinatura');
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
            Arquivo::DISCO_ASSINATURA
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
            Arquivo::DISCO_ASSINATURA
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
