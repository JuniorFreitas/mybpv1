<?php

namespace App\Http\Controllers;

use App\Infrastructure\Ajuda\ManualSecaoHtml;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AjudaController extends Controller
{
    private const MANUAL_DIR = 'docs/manual-completo';

    /**
     * Uma única seção do manual (isolada em iframe), citada como fonte pelo Assistente.
     * O manual completo não é exposto: nem página inteira, nem PDF.
     */
    public function secao(string $ancora, ManualSecaoHtml $manual): Response
    {
        $html = $manual->render($ancora, rtrim(url('/g/ajuda/manual'), '/') . '/');

        if ($html === null) {
            abort(404, 'Conteúdo não encontrado.');
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'private, max-age=300',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'",
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    /**
     * Assets do manual HTML (apenas pasta img/).
     */
    public function asset(string $path): BinaryFileResponse
    {
        $path = str_replace(['\\', "\0"], '', $path);
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '..')) {
            abort(404);
        }

        if (str_starts_with($path, 'img/')) {
            $path = substr($path, 4);
        }

        $baseDir = realpath(base_path(self::MANUAL_DIR . '/img'));
        if ($baseDir === false) {
            abort(404);
        }

        $full = realpath($baseDir . DIRECTORY_SEPARATOR . $path);
        if ($full === false || !str_starts_with($full, $baseDir . DIRECTORY_SEPARATOR) || !is_file($full)) {
            abort(404);
        }

        return response()->file($full, [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
