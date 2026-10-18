<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageS3Controller extends Controller
{
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
            }

            return response()->json([
                'msg' => "O upload do arquivo \"{$request->file('arquivo')->getClientOriginalName()}\" falhou. Permitidos apenas imagens JPG/JPEG ou PDF.",
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
        $arquivo = Arquivo::sanitizarPathStorage($arquivo);
        $model = Arquivo::findByArquivo($arquivo);
        if (!$model || $model->disco !== Arquivo::S3) {
            return response('', 404);
        }

        if (!Storage::disk(Arquivo::S3)->exists($arquivo)) {
            return response('', 404);
        }

        return Storage::disk(Arquivo::S3)->response($arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        $arquivo = Arquivo::sanitizarPathStorage($arquivo);
        $model = Arquivo::findByArquivo($arquivo);
        if (!$model || $model->disco !== Arquivo::S3) {
            return response('', 404);
        }

        if ($model->temporario) {
            Arquivo::apagar($arquivo);
            return response('', 200);
        }

        return response('Não foi possível apagar o anexo', 400);
    }

    public function download(Request $request, $arquivo)
    {
        $arquivo = Arquivo::sanitizarPathStorage($arquivo);
        $model = Arquivo::findByArquivo($arquivo);
        if (!$model || $model->disco !== Arquivo::S3) {
            return response('', 404);
        }

        if (!Storage::disk(Arquivo::S3)->exists($arquivo)) {
            return response('', 404);
        }

        $nome = ($model->nome ?: pathinfo($arquivo, PATHINFO_FILENAME)) . ($model->extensao ?: '');

        return Storage::disk(Arquivo::S3)->download($arquivo, $nome);
    }
}
