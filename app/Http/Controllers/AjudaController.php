<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AjudaController extends Controller
{
    private const MANUAL_DIR = 'docs/manual-completo';

    private const MANUAL_HTML = 'manual.html';

    private const MANUAL_PDF = 'Manual-Completo-MyBPIN.pdf';

    /**
     * Página do manual dentro do layout do sistema.
     */
    public function index(): View
    {
        return view('g.ajuda.manual');
    }

    /**
     * HTML completo do manual (isolado no iframe para não conflitar com o CSS do sistema).
     */
    public function conteudo(): Response
    {
        $path = base_path(self::MANUAL_DIR . '/' . self::MANUAL_HTML);

        if (!is_file($path) || !is_readable($path)) {
            abort(404, 'Manual do usuário não encontrado.');
        }

        $html = file_get_contents($path);
        if ($html === false) {
            abort(500, 'Não foi possível ler o manual.');
        }

        $baseHref = rtrim(url('/g/ajuda/manual'), '/') . '/';

        if (!str_contains($html, '<base ')) {
            $html = preg_replace(
                '/<head(\s[^>]*)?>/i',
                '<head$1><base href="' . e($baseHref) . '">',
                $html,
                1
            ) ?? $html;
        }

        // Remove regras de impressão/A4 que atrapalham a leitura na tela
        $html = preg_replace(
            '/@page[^{]*\{(?:[^{}]++|\{[^{}]*+\})*+\}/s',
            '',
            $html
        ) ?? $html;

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'private, max-age=300',
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

    /**
     * Download/visualização do PDF gerado.
     */
    public function pdf(): BinaryFileResponse|Response
    {
        $path = base_path(self::MANUAL_DIR . '/' . self::MANUAL_PDF);

        if (!is_file($path) || !is_readable($path)) {
            abort(404, 'PDF do manual não encontrado.');
        }

        return response()->download($path, self::MANUAL_PDF, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
