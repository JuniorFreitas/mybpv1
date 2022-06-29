<?php

namespace App\Http\Controllers;

use App\Models\AreaEtiqueta;
use App\Models\CentroCusto;
use App\Models\Cliente;
use App\Models\Sistema;
use App\Models\Vaga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicoController extends Controller
{

    public function download(Request $request, string $nome)
    {
        return Storage::disk('fotos_imovel')->url($nome);
        //return Storage::disk('fotos_imovel')->download($nome,'foto.jpg');
    }

    public function upload(Request $request)
    {

        //dd($request->all());
        if ($request->arquivo->getClientOriginalName() == "Edital da UFMA.pdf") {
            return response()->json([
                'msg' => "O upload do arquivo \"{$request->arquivo->getClientOriginalName()}\" falhou",
                'erros' => []
            ], 400);
        } else {
            return response()->json([
                'id' => '300',
                'nome' => $request->arquivo->getClientOriginalName(),
                'imagem' => true,
                'extensao' => '.jpg',
                'thumb' => 'https://osegredo.com.br/wp-content/uploads/2017/09/O-que-as-pessoas-felizes-t%C3%AAm-em-comum-site-830x450.jpg',
            ], 201);
        }

    }

    public function listaVagas()
    {
        return response()->json(['vagas' => Vaga::whereAtivo(true)->get()], 200);
    }

    public function listaAreasEtiquetas()
    {
        return response()->json(['areas' => AreaEtiqueta::whereAtivo(true)->get()], 200);
    }

    public function listaAreasEtiquetasCliente(Request $request, Cliente $cliente)
    {
        $key = "listaAreasEtiquetasCliente";
//        $data = $cliente->AreasEtiquetas()->whereAtivo(true)->get();
//        $cache = Sistema::getCache($key) ?: Sistema::putCache($key, $data);
        return response()->json($cliente->AreasEtiquetas()->whereAtivo(true)->get());
    }

    public function listaCentroCusto(Request $request)
    {
        $centros = CentroCusto::select(['id','label'])->whereAtivo(true)->get()->transform(function ($item) {
            $item->text = $item->label;
            return $item;
        });
        return ['centro_custos' => $centros];
    }

    public function cnpjbusca(Request $request)
    {
        return \App\Models\Sistema::cnpjSearch($request->cnpj);

    }


}
