<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\GrupoCloud;
use App\Models\ItensCloud;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Mail;
use MasterTag\DataHora;

class ItensCloudController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('cloud_insert');
        $dados = $request->input();

        $regra = Rule::unique('itens_cloud')->where(function ($query) use ($dados) {
            return $query->whereCloudId($dados['cloud_id'])
                ->whereLabel($dados['label'])
                ->whereTipo($dados['tipo'])
                ->whereDeletedAt(null)
                ->wherePertence($dados['pertence']);
        });


        $dadosValidados = \Validator::make($dados, [
            'label' => ['required', $regra]
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao criar nova pasta',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $dados['quem_criou'] = auth()->id();
                $item = ItensCloud::create($dados);
                $permissoes = collect([GrupoCloud::GRUPOADMIN,GrupoCloud::GRUPOADMINFINANCEIRO]);
                if ($request->filled('permissoes')) {
                    $dadosPermissao = [];
                    foreach ($dados['permissoes'] as $grupo_cloud) {
                        $dadosPermissao[] = $grupo_cloud['id'];
                    }
                    $permissoes = $permissoes->concat($dadosPermissao);
                }
                $item->Permissoes()->attach($permissoes);
                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                return response($e->getMessage(), 400);
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ItensCloud $itenscloud
     * @return ItensCloud
     */
    public function edit(ItensCloud $itenscloud)
    {
        $iteCloud = $itenscloud;
        $iteCloud->permissoes = $itenscloud->Permissoes->transform(function ($i) {
            $i->permitido = true;
            return $i;
        });
        return $iteCloud;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ItensCloud $itenscloud
     * @return ItensCloud
     */
    public function update(Request $request, ItensCloud $itenscloud)
    {
        //UpdatePara Pasta
        $this->authorize('cloud_update');
        $dados = $request->input();

        $regra = Rule::unique('itens_cloud')->where(function ($query) use ($dados) {
            return $query->whereCloudId($dados['cloud_id'])
                ->whereLabel($dados['label'])
                ->whereTipo($dados['tipo'])
                ->whereDeletedAt(null)
                ->wherePertence($dados['pertence']);
        })->ignore($itenscloud->id);

        $dadosValidados = \Validator::make($dados, [
            'label' => ['required', $regra]
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao alterar pasta',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $dados['quem_editou'] = auth()->id(); // pego quem editou

                $permissoes = collect([GrupoCloud::GRUPOADMIN,GrupoCloud::GRUPOADMINFINANCEIRO]); // seto a permissao grupo "todos (root)"

                //Verifico se tem algum grupo marcado
                if ($request->filled('permissoes')) {
                    $dadosPermissao = []; // inicio um array vazio para fazer o loop
                    foreach ($dados['permissoes'] as $grupo_cloud) {
                        $dadosPermissao[] = $grupo_cloud['id'];
                    }
                    $permissoes = $permissoes->concat($dadosPermissao);
                }

                $permissoes = $permissoes->unique()->values();

                $itenscloud->update($dados); // atualizo o item

                //Permissão da pasta ou arquivo que está editando
                $itenscloud->Permissoes()->sync($permissoes); // Permissão Inicial

                // Permissões dos arquivos ou pastas que estão dentro da pasta que foi editada (permissão)
                $arquivos = ItensCloud::wherePertence($itenscloud->id)->select('id', 'pertence');

                if ($arquivos->count() > 0) {
                    foreach ($arquivos->get() as $arquivoAtual) {
                        $arquivoAtual->Permissoes()->sync($permissoes);
                        $arquivoAtual->recursivo($permissoes);
                    }
                }

                DB::commit();
                return response()->json(['successo'], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                return response($e->getMessage(), 400);
            }
        }
    }

    public function Recursivo($modelAtual, $permissoes)
    {
//        if ($modelAtual->pertence) {
//            $modelRecursiva = ItensCloud::whereId($modelAtual->pertence)->select('id', 'pertence')->first();
//            $modelRecursiva->Permissoes()->sync($permissoes);
//            $this->Recursivo($modelRecursiva, $permissoes);
//        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\ItensCloud $itenscloud
     * @return \Illuminate\Http\Response
     */
    public function destroy(ItensCloud $itenscloud)
    {
        try {
            DB::beginTransaction();
            if ($itenscloud->deleted_at !== null) {
                if ($itenscloud->tipo == 'arquivo') {
                    return response()->json(['msg' => "Não foi possível apagar o arquivo ({$itenscloud->label}), pois foi apgagado por {$itenscloud->Excluiu->nome} em {$itenscloud->deleted_at}"], 400);
                } else {
                    return response()->json(['msg' => "Não foi possível apagar a pasta ({$itenscloud->label}), pois foi apgagada por {$itenscloud->Excluiu->nome} em {$itenscloud->deleted_at}"], 400);
                }
            }

            if ($itenscloud->tipo == 'arquivo') {
                $itenscloud->update(['quem_excluiu' => auth()->id()]);
                $itenscloud->delete();
            } else {
                //Se for Pasta
                $itenscloud->update(['quem_excluiu' => auth()->id()]);
                $itenscloud->delete();
                $itenscloud->deleteRecursivo();
            }
            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response($e->getMessage(), 400);
        }
    }

    public function revisar(ItensCloud $item)
    {
        try {
            $agora = new DataHora();
            DB::beginTransaction();
            if ($item->revisado) {
                return response()->json(['msg' => "Não foi possível revisar o arquivo ({$item->label}), pois foi revisado por {$item->Revisou->nome} em {$item->data_revisao}"], 400);
            }
            $dados = [
                'aprovado' => false,
                'quem_aprovou' => null,
                'revisado' => true,
                'quem_revisou' => auth()->id(),
                'data_revisao' => $agora->dataHoraInsert()
            ];

            $item->update($dados);
            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response($e->getMessage(), 400);
        }
    }

    public function enviarRevisao(Request $request)
    {

        $dados = $request->input();
        $dados['quem_enviou'] = User::find(auth()->id())->nome;
        $dados['texto_livre'] = isset($dados['texto_livre']) ? $dados['texto_livre'] : '';
        try {
            Mail::send('email.cloud.revisao', $dados, function ($m) use ($dados) {
                $m->from('naoresponda@mybp.com.br', 'MyBP');
                $m->subject("CLOUD item Revisão - {$dados['quem_enviou']}");
                $m->to(mb_strtolower($dados['email']));
            });
            return response()->json(['enviado' => true], 200);
        } catch (\Exception $e) {
            \Log::debug("Error ao enviar e-maill de Revisão no Cloud: {$e->getMessage()}, {$e->getFile()}, {$e->getLine()}, {$e->getCode()}, {$e->getTrace()} ");
            return response()->json(['enviado' => false], 400);
        }
    }

    public function enviarAprovacao(Request $request)
    {

        $dados = $request->input();
        $dados['quem_enviou'] = User::find(auth()->id())->nome;
        $dados['texto_livre'] = isset($dados['texto_livre']) ? $dados['texto_livre'] : '';
        try {
            Mail::send('email.cloud.aprovacao', $dados, function ($m) use ($dados) {
                $m->from('naoresponda@mybp.com.br', 'MyBP');
                $m->subject("CLOUD item Aprovação - {$dados['quem_enviou']}");
                $m->to(mb_strtolower($dados['email']));
            });
            return response()->json(['enviado' => true], 200);
        } catch (\Exception $e) {
            \Log::debug("Error ao enviar e-maill de Aprovação no Cloud: {$e->getMessage()}, {$e->getFile()}, {$e->getLine()}, {$e->getCode()}, {$e->getTrace()} ");
            return response()->json(['enviado' => false], 400);
        }
    }

    public function aprovar(ItensCloud $item)
    {
        try {
            $agora = new DataHora();
            DB::beginTransaction();
            if ($item->aprovado) {
                return response()->json(['msg' => "Não foi possível aprovar o arquivo ({$item->label}), pois foi aprovado por {$item->Aprovou->nome} em {$item->data_aprovacao}"], 400);
            }

            if (!$item->revisado) {
                $dados = [
                    'quem_revisou' => auth()->id(),
                    'revisado' => true,
                    'data_revisao' => $agora->dataHoraInsert()
                ];
                $item->update($dados);
            }

            $dados = [
                'aprovado' => true,
                'quem_aprovou' => auth()->id(),
                'data_aprovacao' => $agora->dataHoraInsert()
            ];

            $item->update($dados);
            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response($e->getMessage(), 400);
        }
    }

    public function moverEstruturaPasta(Request $request, $cloud, $id = null)
    {
        $resultado = ItensCloud::whereCloudId($cloud)->whereTipo('pasta')->with('Pertence', 'Arquivo', 'Criou', 'Editou');


        if ($id == 'null') {
            $resultado->whereNull('pertence');
            $anterior = null;
            $atualNome = null;
            $atual_id = null;
            $resultado = $resultado->orderBy('label')->get();
            $resultado->transform(function (ItensCloud $item) {
                $item->append('TemPermissao');
                return $item;
            });
        }
        if ($id != 'null') {
            $itemBusca = ItensCloud::find($id);
            $anterior = $itemBusca->pertence;
            $atualNome = $itemBusca->label;
            $atual_id = $itemBusca->id;

            if ($itemBusca->TemPermissao) {
                $resultado->wherePertence($id);
            } else {
                return response()->json(['msg' => 'Sem permissao para acessar a pasta',], 403);
            }

            $resultado = $resultado->orderBy('label')->get();

            $resultado->transform(function (ItensCloud $item) {
                $item->append('TemPermissao');
                $item->pertence_id = $item->pertence;
                return $item;
            });
        }

        return response()->json([
            'lista' => $resultado,
            'anterior' => $anterior,
            'nomePasta' => $atualNome,
            'atual_id' => $atual_id,
        ]);
    }

    public function moverArquivo(Request $request, ItensCloud $item)
    {
        try {
            $agora = new DataHora();
            if ($item->pertence == $request->inicial) {
                DB::beginTransaction();
                $dados = [
                    'pertence' => $request->pasta,
                    'quem_moveu' => auth()->id(),
                    'pertence_anterior' => $request->inicial,
                    'data_movido' => $agora->dataHoraInsert()
                ];
                $item->update($dados);
                DB::commit();
                return response()->json([], 201);
            } else {
                return response()->json(['msg' => "Não foi possível mover o arquivo ({$item->label}), pois foi movido por {$item->Moveu->nome} em {$item->data_movido}"], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response($e->getMessage(), 400);
        }
    }

    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        if ($request->file('arquivo')->isValid()) {
            $mimeType = $request->file('arquivo')->getMimeType();

            $permitidos = Arquivo::MIMESTODOS;

            if (in_array($mimeType, $permitidos)) {
                \DB::beginTransaction();

                $extensao = $request->file('arquivo')->extension();
                $tmExt = strlen('.' . $extensao);

                $nome = substr($request->file('arquivo')->getClientOriginalName(), 0, -$tmExt);

                $item = ItensCloud::whereCloudId($request->cloud_id)
                    ->whereLabel($nome)
                    ->whereTipo('arquivo')
                    ->whereDeletedAt(null)
                    ->wherePertence($request->pertence_id)
                    ->whereHas('Arquivo', function ($q) use ($extensao) {
                        $q->whereExtensao('.' . $extensao);
                    })
                    ->count();

                if ($item > 0) {
                    return response()->json(['msg' => 'Arquivo ja existe!'], 400);
                }

                $arquivo = Arquivo::gravaArquivoReal($request, 'arquivo', Arquivo::DISCO_CLOUD);

                $arquivo->temporario = false;
                $arquivo->chave = '';
                $arquivo->save();

                $dadosUpload = [
                    'cloud_id' => $request->cloud_id,
                    'arquivo_id' => $arquivo->id,
                    'label' => $arquivo->nome,
                    'tipo' => 'arquivo',
                    'pertence' => $request->pertence_id,
                    'quem_criou' => auth()->id(),
                ];

                $item = ItensCloud::create($dadosUpload);
                $pasta = ItensCloud::find($request->pertence_id);
                $permissoes = $pasta->Permissoes()->pluck('grupo_cloud_id');
                $item->Permissoes()->attach($permissoes);

                //get de permissoes da pasta
                \DB::commit();
                return response()->json($arquivo, 201);
            }

            \DB::rollBack();
            return response()->json([
                'msg' => "O upload do arquivo \"{$request->file('arquivo')->getClientOriginalName()}\" falhou. Arquivo não permitido.",
                'erros' => []
            ], 400);
        }

        return response()->json([
            'msg' => "O upload do anexo falhou",
            'erros' => []
        ], 400);
    }

    public function uploadAtualizarAnexos(Request $request)
    {
        if ($request->file('arquivo')->isValid()) {
            $mimeType = $request->file('arquivo')->getMimeType();

            $permitidos = Arquivo::MIMESTODOS;

            if (in_array($mimeType, $permitidos)) {
                \DB::beginTransaction();

                $arquivo = Arquivo::gravaArquivoReal($request, 'arquivo', Arquivo::DISCO_CLOUD);
                $arquivo->temporario = false;
                $arquivo->chave = '';
                $arquivo->save();

                $dadosUpload = [
                    'arquivo_id' => $arquivo->id,
                    'quem_editou' => auth()->id(),
                    'revisado' => false,
                    'quem_revisou' => null,
                    'data_revisao' => null,
                    'aprovado' => false,
                    'quem_aprovou' => null,
                    'data_aprovacao' => null,
                ];

                ItensCloud::whereArquivoId($request->anterior_id)->update($dadosUpload);
                Arquivo::find($request->anterior_id)->excluir();

                \DB::commit();
                return response()->json($arquivo, 201);
            }
            \DB::rollBack();
            return response()->json([
                'msg' => "O upload do arquivo \"{$request->file('arquivo')->getClientOriginalName()}\" falhou. Arquivo não permitido.",
                'erros' => []
            ], 400);
        }
        return response()->json([
            'msg' => "O upload do anexo falhou",
            'erros' => []
        ], 400);

    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow([Arquivo::DISCO_CLOUD], $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete([Arquivo::DISCO_CLOUD], $arquivo);
    }

    //anexo ou foto
    public function download($arquivo)
    {
        return Arquivo::anexoDownload([Arquivo::DISCO_CLOUD], $arquivo);
    }


}
