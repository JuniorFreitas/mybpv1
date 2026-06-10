<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Arquivo;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class IntegracaoSpaMediaController extends Controller
{
    /**
     * Proxy do logotipo da empresa por apelido. Não expõe URL direta do disco/CDN.
     */
    public function logotipo(Request $request, string $apelido): Response
    {
        $thumb = in_array((string) $request->query('thumb', '0'), ['1', 'true', 'on'], true);

        $cliente = Cliente::withoutGlobalScopes()
            ->where('apelido', $apelido)
            ->where('ativo', true)
            ->first();

        if ($cliente === null) {
            return response('Não encontrado.', 404);
        }

        $arquivo = $cliente->Logo()->first();

        if ($arquivo === null || ! in_array($arquivo->disco, Arquivo::LISTAGEM_DISCOS, true)) {
            return response('Não encontrado.', 404);
        }

        $disk = Storage::disk($arquivo->disco);

        $path = $thumb && $arquivo->thumb
            ? $arquivo->thumb
            : $arquivo->file;

        if ($path === null || $path === '') {
            return response('Não encontrado.', 404);
        }

        if ($thumb && $arquivo->thumb && ! $disk->exists($path)) {
            $path = $arquivo->file;
        }

        if ($path === null || $path === '' || ! $disk->exists($path)) {
            return response('Não encontrado.', 404);
        }

        $mime = $this->mimePorExtensao($arquivo->extensao);

        return $disk->response($path, $arquivo->nome, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    private function mimePorExtensao(?string $extensao): string
    {
        return match (strtolower((string) $extensao)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'application/octet-stream',
        };
    }
}
