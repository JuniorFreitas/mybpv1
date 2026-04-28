<?php

namespace App\Services\Cbo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CboCsvDownloader
{
    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    public function downloadTo(string $url, string $destinationAbsolutePath): void
    {
        $dir = dirname($destinationAbsolutePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $response = Http::withHeaders([
            'User-Agent' => self::USER_AGENT,
            'Accept' => 'text/csv,*/*;q=0.8',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
        ])
            ->timeout(180)
            ->retry(3, 2000)
            ->get($url);

        if (! $response->successful()) {
            Log::error('CBO: falha ao baixar CSV', [
                'url' => $url,
                'status' => $response->status(),
            ]);
            throw new \RuntimeException("Download falhou (HTTP {$response->status()}): {$url}");
        }

        $body = $response->body();
        $this->assertBodyLooksLikeCsv($body, $url);

        if (file_put_contents($destinationAbsolutePath, $body) === false) {
            throw new \RuntimeException("Não foi possível gravar o arquivo: {$destinationAbsolutePath}");
        }

        Log::info('CBO: CSV baixado', [
            'url' => $url,
            'destino' => $destinationAbsolutePath,
            'bytes' => strlen($body),
        ]);
    }

    /**
     * Evita gravar HTML de erro, WAF ou página de login como se fosse CSV.
     */
    private function assertBodyLooksLikeCsv(string $body, string $url): void
    {
        $trim = ltrim($body);
        if ($trim === '') {
            throw new \RuntimeException("Download retornou corpo vazio: {$url}");
        }

        if (
            str_starts_with($trim, '<')
            && (stripos($trim, '<!DOCTYPE') !== false || stripos($trim, '<html') !== false)
        ) {
            Log::error('CBO: URL retornou HTML em vez de CSV', ['url' => $url]);
            throw new \RuntimeException(
                "A URL retornou HTML (bloqueio, erro ou página incorreta), não um CSV. Verifique CBO_*_CSV_URL ou o acesso à fonte: {$url}"
            );
        }
    }

    public function fetchHtml(string $url): string
    {
        $response = Http::withHeaders([
            'User-Agent' => self::USER_AGENT,
            'Accept' => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
        ])
            ->timeout(120)
            ->retry(3, 2000)
            ->get($url);

        if (! $response->successful()) {
            Log::error('CBO: falha ao obter página de downloads', [
                'url' => $url,
                'status' => $response->status(),
            ]);
            throw new \RuntimeException("Falha ao acessar página de downloads (HTTP {$response->status()}): {$url}");
        }

        return $response->body();
    }

    public function storagePath(string $filename): string
    {
        return storage_path('app/cbo/' . ltrim($filename, '/'));
    }

    public function shouldUseCache(string $path, bool $force): bool
    {
        if ($force) {
            return false;
        }
        if (! is_file($path)) {
            return false;
        }

        $minBytes = (int) (config('services.cbo.min_csv_bytes_for_cache') ?? 2000);
        if ($minBytes > 0 && filesize($path) < $minBytes) {
            Log::info('CBO: ignorando cache (arquivo pequeno demais)', [
                'path' => $path,
                'bytes' => filesize($path),
                'minimo' => $minBytes,
            ]);

            return false;
        }

        return (time() - filemtime($path)) < 86400;
    }
}
