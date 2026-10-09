<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $model = $this->resolverArquivoS3($arquivo);
        if (!$model || !$this->usuarioPodeAcessar($model)) {
            return response('', 404);
        }

        if (!Storage::disk(Arquivo::S3)->exists($arquivo)) {
            return response('', 404);
        }

        return Storage::disk(Arquivo::S3)->response($arquivo, null, [
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'inline',
        ]);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        $model = $this->resolverArquivoS3($arquivo);
        if (!$model || !$this->usuarioPodeAcessar($model)) {
            return response('', 404);
        }

        if ($model->temporario && (int) $model->quem_enviou === (int) auth()->id()) {
            $apagou = Arquivo::anexoDelete(Arquivo::S3, $arquivo);
            if ($apagou === true) {
                return response('', 200);
            }
        }

        return response('Não foi possível apagar o anexo', 400);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        $model = $this->resolverArquivoS3($arquivo);
        if (!$model || !$this->usuarioPodeAcessar($model)) {
            return response('', 404);
        }

        if (!Storage::disk(Arquivo::S3)->exists($arquivo)) {
            return response('', 404);
        }

        $nome = ($model->nome ?: pathinfo($arquivo, PATHINFO_FILENAME)) . ($model->extensao ?: '');

        return Storage::disk(Arquivo::S3)->download($arquivo, $nome, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function resolverArquivoS3(string $arquivo): ?Arquivo
    {
        return Arquivo::query()
            ->where('disco', Arquivo::S3)
            ->where(function ($q) use ($arquivo) {
                $q->where('file', $arquivo)->orWhere('thumb', $arquivo);
            })
            ->first();
    }

    private function usuarioPodeAcessar(Arquivo $model): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        if ((int) $model->quem_enviou === (int) $user->id) {
            return true;
        }

        // Candidato só acessa o próprio arquivo
        if ($user->tipo === User::CANDIDATO) {
            return false;
        }

        $uploaderEmpresa = User::withoutGlobalScopes()
            ->where('id', $model->quem_enviou)
            ->value('empresa_id');

        return $uploaderEmpresa !== null
            && (int) $uploaderEmpresa === (int) $user->empresa_id;
    }
}
