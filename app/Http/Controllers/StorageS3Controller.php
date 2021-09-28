<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use Illuminate\Http\Request;

class StorageS3Controller extends Controller
{
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
                $arquivo = Arquivo::gravaArquivo($request, 'arquivo', Arquivo::S3);
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
            $disco = Arquivo::nomeDisco($arquivo);
            $permitidos = [
                Arquivo::S3
            ];

            if (in_array($disco, $permitidos) == false) {
                return response("", 404);
            }


            return \Storage::disk($disco)->response($arquivo);
        }
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        //Se esta apagando realmente um anexo_imovel
        $disco = Arquivo::nomeDisco($arquivo);
        $permitidos = [
            Arquivo::S3
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
            Arquivo::S3
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
