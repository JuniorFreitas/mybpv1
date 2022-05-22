<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\LogoCliente;
use DB;
use Illuminate\Http\Request;

class ClienteLogoSiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.site.clientes.index');
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
//        $this->authorize('site_galeria_site_insert');
        $dados = $request->input();
        $cliente = LogoCliente::find(1);
        try {
            DB::beginTransaction();

            if (isset($dados['fotosDel'])) {
                foreach ($dados['fotosDel'] as $id_anexo) {
                    $arquivo = Arquivo::find($id_anexo);
                    $arquivo->excluir();
                }
            }

            if (isset($dados['fotos'])) {
                foreach ($dados['fotos'] as $index => $foto) {
                    $arquivo = Arquivo::whereChave($foto['chave'])->whereId($foto['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $cliente->Fotos()->attach($arquivo->id, ['ordem' => $index]);
                    }
                }
            }

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'msg' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\LogoCliente $cliente
     * @return \Illuminate\Http\Response
     */
    public function show(LogoCliente $cliente)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\LogoCliente $cliente
     * @return \Illuminate\Http\Response
     */
    public function edit(LogoCliente $cliente)
    {
        $cliente = LogoCliente::find(1);
        return $cliente->load('Fotos');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\LogoCliente $cliente
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LogoCliente $cliente)
    {
        $cliente2 = LogoCliente::find(1);

//        $this->authorize('site_galeria_site_update');
        $dados = $request->input();
        try {

            DB::beginTransaction();

            // Fotos
            if (isset($dados['fotosDel'])) {
                foreach ($dados['fotosDel'] as $id_foto) {
                    $arquivo = Arquivo::find($id_foto);
                    $arquivo->excluir();
                }
            }

            if (isset($dados['fotos'])) {
                foreach ($dados['fotos'] as $index => $foto) {
//                    dd($foto);
                    //Se nao tem chave, entao é uma foto que já estava cadastrada no banco
                    if ($foto['chave'] == null) {
                        Arquivo::whereId($foto['id'])->update([
                            'nome' => $foto['nome'],
                        ]);
                        $cliente2->Fotos()->updateExistingPivot($foto['id'], ['ordem' => $index]);
                    } else {
                        $arquivo = Arquivo::whereChave($foto['chave'])->whereId($foto['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $cliente2->Fotos()->attach($arquivo->id, ['ordem' => $index]);
                        }
                    }

                }
            }

            DB::commit();
            return response()->json([], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'msg' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\LogoCliente $cliente
     * @return \Illuminate\Http\Response
     */
    public function destroy(LogoCliente $cliente)
    {
        //
    }

    public function fotoUpload(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENS, Arquivo::DISCO_SERVICO_FORNECEDOR);
    }

    public function fotoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_PUBLICO, $arquivo);
    }

    public function fotoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_PUBLICO, $arquivo);

    }

    //anexo ou foto
    public function fotoDownload(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_PUBLICO, $arquivo);
    }

    public function atualizar()
    {
        return $logo = LogoCliente::get();
//        return response()->json($logo, 200);
    }
}
