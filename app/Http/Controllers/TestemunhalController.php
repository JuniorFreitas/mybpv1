<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Testemunhal;
use Illuminate\Http\Request;

class TestemunhalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.site.testemunhal.index');
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required',
            'texto' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Depoimento',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            if (!isset($dados['anexo'])) {
                return response()->json([
                    'msg' => 'Faça o upload de uma imagem',
                    'erros' => $dadosValidados->errors()
                ], 400);
            }

            $dados['texto'] = html_entity_decode($dados['texto']);
            $dados['texto'] = strip_tags($dados['texto'], "<p><a><strong><i><ul><li><ol><table><tbody><tr><td>"); // permitir apenas essas tags

            $testemunhal = Testemunhal::create($dados);
            foreach ($dados['anexo'] as $index => $anexo) {
                $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                if ($arquivo) {
                    $arquivo->temporario = false;
                    $arquivo->chave = '';
                    $arquivo->save();
                    $testemunhal->Anexo()->attach($arquivo->id);
                }
            }

            return response()->json([], 201);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Testemunhal $testemunhal
     * @return \Illuminate\Http\Response
     */
    public function show(Testemunhal $testemunhal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Testemunhal $testemunhal
     * @return \Illuminate\Http\Response
     */
    public function edit(Testemunhal $testemunhal)
    {
        return $testemunhal->load('Anexo');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Testemunhal $testemunhal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Testemunhal $testemunhal)
    {
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required',
            'texto' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Depoimento',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {

            if (isset($dados['anexoDel'])) {
                foreach ($dados['anexoDel'] as $id_anexo) {
                    $arquivo = Arquivo::find($id_anexo);
                    $arquivo->excluir();
                }
            }

            if (isset($dados['anexo'])) {
                foreach ($dados['anexo'] as $index => $anexo) {
                    //Se nao tem chave, entao é uma anexo que já estava cadastrada no banco
                    if ($anexo['chave'] == null) {
                        Arquivo::whereId($anexo['id'])->update([
                            'nome' => $anexo['nome'],
                        ]);
                    } else {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $testemunhal->Anexo()->attach($arquivo->id);
                        }
                    }

                }
            }

            $testemunhal->update($dados);
            return response()->json([], 201);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Testemunhal $testemunhal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Testemunhal $testemunhal)
    {
        if ($testemunhal->Anexo->count() > 0) {
            foreach ($testemunhal->Anexo as $anexo) {
                $anexo->excluir($anexo->id);
            }
        }
        $testemunhal->delete();
    }

    public function atualizar(Request $request)
    {
        $resultado = Testemunhal::with('Anexo');

        if ($request->filled('campoBusca')) {
            $resultado->where('titulo', 'like', '%' . $request->campoBusca . '%');
        }

        $resultado = $resultado->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $resultado->items()
        ]);

    }

    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENS, Arquivo::DISCO_PUBLICO);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_PUBLICO, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_PUBLICO, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_PUBLICO, $arquivo);
    }
}
