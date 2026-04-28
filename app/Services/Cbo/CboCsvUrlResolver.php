<?php

namespace App\Services\Cbo;

use DOMDocument;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Support\Facades\Log;

class CboCsvUrlResolver
{
    public function __construct(
        private readonly CboCsvDownloader $downloader,
    ) {}

    /**
     * @return array{familias: string, ocupacoes: string, perfil: ?string}
     */
    public function resolve(): array
    {
        $config = config('services.cbo', []);

        $pageUrl = $config['download_page'] ?? 'https://www.gov.br/trabalho-e-emprego/pt-br/assuntos/cbo/servicos/downloads';

        $familiasEnv = $this->nonEmptyString($config['familias_csv_url'] ?? null);
        $ocupacoesEnv = $this->nonEmptyString($config['ocupacoes_csv_url'] ?? null);
        $perfilEnv = $this->nonEmptyString($config['perfil_csv_url'] ?? null);

        if ($familiasEnv !== null && $ocupacoesEnv !== null) {
            $famAbs = $this->ensureAbsoluteCsvUrl($familiasEnv, $pageUrl);
            $ocuAbs = $this->ensureAbsoluteCsvUrl($ocupacoesEnv, $pageUrl);
            $perAbs = $perfilEnv !== null ? $this->ensureAbsoluteCsvUrl($perfilEnv, $pageUrl) : null;

            Log::info('CBO: URLs de famílias e ocupações obtidas via configuração (.env)', [
                'familias' => $famAbs,
                'ocupacoes' => $ocuAbs,
                'perfil' => $perAbs,
            ]);

            return [
                'familias' => $famAbs,
                'ocupacoes' => $ocuAbs,
                'perfil' => $perAbs,
            ];
        }

        Log::info('CBO: resolvendo links faltantes a partir da página oficial', ['page' => $pageUrl]);

        $html = $this->downloader->fetchHtml($pageUrl);
        $candidates = $this->collectCandidates($html, $pageUrl);

        foreach ($candidates as $c) {
            Log::debug('CBO: link CSV candidato', $c);
        }

        $ocupUrl = $ocupacoesEnv !== null
            ? $this->ensureAbsoluteCsvUrl($ocupacoesEnv, $pageUrl)
            : ($this->pickBest($candidates, 'ocupacao') ?? $this->pickFallbackByPath($candidates, ['ocupacao', 'ocupac']));
        $famUrl = $familiasEnv !== null
            ? $this->ensureAbsoluteCsvUrl($familiasEnv, $pageUrl)
            : ($this->pickBest($candidates, 'familia') ?? $this->pickFallbackByPath($candidates, ['familia', 'famil']));
        $perfilUrl = $perfilEnv !== null
            ? $this->ensureAbsoluteCsvUrl($perfilEnv, $pageUrl)
            : ($this->pickBest($candidates, 'perfil') ?? $this->pickFallbackByPath($candidates, ['perfilocupacional', 'perfil', 'sumaria', 'sumario']));

        if ($ocupUrl === null || $famUrl === null) {
            Log::error('CBO: não foi possível obter URLs de ocupação e/ou família', [
                'ocupacao' => $ocupUrl,
                'familia' => $famUrl,
            ]);
            throw new \RuntimeException(
                'Não foi possível localizar os CSVs oficiais. Defina CBO_OCUPACOES_CSV_URL e CBO_FAMILIAS_CSV_URL no .env ou verifique o acesso à página de downloads.'
            );
        }

        Log::info('CBO: URLs finais para importação', [
            'familias' => $famUrl,
            'ocupacoes' => $ocupUrl,
            'perfil' => $perfilUrl,
        ]);

        return [
            'familias' => $famUrl,
            'ocupacoes' => $ocupUrl,
            'perfil' => $perfilUrl,
        ];
    }

    /**
     * Aceita URL absoluta ou caminho/nome de arquivo relativo à página de downloads
     * (ex.: cbo2002-ocupacao.csv). A base deve terminar com "/" para resolução correta.
     */
    private function ensureAbsoluteCsvUrl(string $urlOrPath, string $downloadPageUrl): string
    {
        $urlOrPath = trim($urlOrPath);
        if (preg_match('#^https?://#i', $urlOrPath)) {
            return $urlOrPath;
        }
        if (str_starts_with($urlOrPath, '//')) {
            return 'https:' . $urlOrPath;
        }

        return (string) UriResolver::resolve(
            new Uri($this->normalizeBaseUrl($downloadPageUrl)),
            new Uri($urlOrPath)
        );
    }

    private function normalizeBaseUrl(string $url): string
    {
        return rtrim($url, '/') . '/';
    }

    /**
     * @param list<array{url: string, text: string, href: string, score_ocupacao: int, score_familia: int, score_perfil: int}> $candidates
     * @param list<string> $pathFragments fragmentos em minúsculas presentes na URL
     */
    private function pickFallbackByPath(array $candidates, array $pathFragments): ?string
    {
        foreach ($pathFragments as $frag) {
            $frag = strtolower($frag);
            foreach ($candidates as $c) {
                if (str_contains(strtolower($c['url']), $frag)) {
                    Log::info('CBO: URL escolhida por fallback (trecho no path)', ['url' => $c['url'], 'trecho' => $frag]);

                    return $c['url'];
                }
            }
        }

        return null;
    }

    /**
     * @return list<array{url: string, text: string, href: string, score_ocupacao: int, score_familia: int, score_perfil: int}>
     */
    private function collectCandidates(string $html, string $pageUrl): array
    {
        $byUrl = [];
        foreach ($this->extractCsvLinksFromDom($html, $pageUrl) as $c) {
            $byUrl[$c['url']] = $c;
        }

        if (preg_match_all('#https?://[^\s"\'<>]+\.csv(?:\?[^\s"\'<>]*)?#iu', $html, $matches)) {
            foreach (array_unique($matches[0]) as $rawUrl) {
                $rawUrl = trim($rawUrl);
                if ($rawUrl === '' || isset($byUrl[$rawUrl])) {
                    continue;
                }
                $lower = mb_strtolower($rawUrl);
                $byUrl[$rawUrl] = [
                    'url' => $rawUrl,
                    'text' => '',
                    'href' => $lower,
                    'score_ocupacao' => $this->scoreOcupacao('', $lower),
                    'score_familia' => $this->scoreFamilia('', $lower),
                    'score_perfil' => $this->scorePerfil('', $lower),
                ];
            }
        }

        return array_values($byUrl);
    }

    private function nonEmptyString(?string $v): ?string
    {
        if ($v === null) {
            return null;
        }
        $t = trim($v);

        return $t !== '' ? $t : null;
    }

    /**
     * @return list<array{url: string, text: string, href: string, score_ocupacao: int, score_familia: int, score_perfil: int}>
     */
    private function extractCsvLinksFromDom(string $html, string $pageUrl): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        $out = [];
        foreach ($dom->getElementsByTagName('a') as $a) {
            $href = trim($a->getAttribute('href'));
            if ($href === '') {
                continue;
            }
            if (! preg_match('/\.csv(\?.*)?$/i', $href) && stripos($href, '.csv') === false) {
                continue;
            }
            $absolute = $this->absoluteUrl($pageUrl, $href);
            $text = mb_strtolower(trim($a->textContent));
            $hrefLower = mb_strtolower($href);
            $urlLower = mb_strtolower($absolute);

            $out[] = [
                'url' => $absolute,
                'text' => $text,
                'href' => $hrefLower,
                'score_ocupacao' => max($this->scoreOcupacao($text, $hrefLower), $this->scoreOcupacao('', $urlLower)),
                'score_familia' => max($this->scoreFamilia($text, $hrefLower), $this->scoreFamilia('', $urlLower)),
                'score_perfil' => max($this->scorePerfil($text, $hrefLower), $this->scorePerfil('', $urlLower)),
            ];
        }

        return $out;
    }

    private function scoreOcupacao(string $text, string $href): int
    {
        $hay = $text . ' ' . $href;
        $s = 0;
        if (preg_match('/ocupa(ç|c)ao|ocupa\\b/i', $hay)) {
            $s += 4;
        }
        if (preg_match('/ocupa/i', $hay) && ! preg_match('/fam(í|i)lia/i', $hay)) {
            $s += 2;
        }
        if (preg_match('/fam(í|i)lia/i', $hay) && ! preg_match('/ocupa/i', $hay)) {
            $s -= 3;
        }
        if (preg_match('/\\.csv/i', $href)) {
            $s += 1;
        }

        return $s;
    }

    private function scoreFamilia(string $text, string $href): int
    {
        $hay = $text . ' ' . $href;
        $s = 0;
        if (preg_match('/fam(í|i)lia/i', $hay)) {
            $s += 5;
        }
        if (preg_match('/ocupa(ç|c)ao|ocupa\\b/i', $hay) && ! preg_match('/fam(í|i)lia/i', $hay)) {
            $s -= 4;
        }
        if (preg_match('/\\.csv/i', $href)) {
            $s += 1;
        }

        return $s;
    }

    private function scorePerfil(string $text, string $href): int
    {
        $hay = $text . ' ' . $href;
        $s = 0;
        if (preg_match('/perfil|sum(á|a)ria|descri(ç|c)ao/i', $hay)) {
            $s += 5;
        }
        if (preg_match('/\\.csv/i', $href)) {
            $s += 1;
        }

        return $s;
    }

    /**
     * @param list<array{url: string, text: string, href: string, score_ocupacao: int, score_familia: int, score_perfil: int}> $candidates
     */
    private function pickBest(array $candidates, string $tipo): ?string
    {
        $key = match ($tipo) {
            'ocupacao' => 'score_ocupacao',
            'familia' => 'score_familia',
            'perfil' => 'score_perfil',
            default => throw new \InvalidArgumentException($tipo),
        };

        $bestUrl = null;
        $bestScore = 0;
        foreach ($candidates as $c) {
            $score = (int) ($c[$key] ?? 0);
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestUrl = $c['url'];
            }
        }

        return $bestScore > 0 ? $bestUrl : null;
    }

    private function absoluteUrl(string $pageUrl, string $href): string
    {
        if (preg_match('#^https?://#i', $href)) {
            return $href;
        }
        if (str_starts_with($href, '//')) {
            return 'https:' . $href;
        }

        return (string) UriResolver::resolve(
            new Uri($this->normalizeBaseUrl($pageUrl)),
            new Uri($href)
        );
    }
}
