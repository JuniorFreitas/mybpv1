<?php

namespace App\Services\Treinamento;

use App\Models\Arquivo;
use App\Models\CarteiraAssinatura;
use App\Models\Sistema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

/**
 * Cache de imagens da carteira de treinamento em base64 para melhorar performance do PDF.
 * Invalidação: chaves incluem path+filemtime (arquivos públicos) ou id+updated_at (assinaturas).
 */
class CarteiraImagemCache
{
    private const PREFIX_PUBLIC = 'carteira_img_b64:';
    private const PREFIX_ASSINATURA = 'carteira_assinatura_b64:';
    private const PREFIX_FOTO_PRINT = 'carteira_foto_print_jpg:';
    private const TTL_DAYS = 30;

    /** TTL menor para miniatura de impressão: renova rápido se a foto for substituída. */
    private const TTL_FOTO_PRINT_DAYS = 7;

    /** Memo por request — evita N× Redis/file para o mesmo path na mesma geração. */
    private static array $memoPublic = [];
    private static array $memoAssinatura = [];
    private static array $memoFoto = [];

    /**
     * Converte imagem do diretório public para base64 (data:image/...;base64,...) com cache.
     * Chave de cache inclui filemtime: ao atualizar o arquivo, o cache é naturalmente renovado.
     *
     * @param string $pathRelativo Caminho relativo ao public (ex: images/carteira/cabecalho_carteira_alumar.webp)
     * @return string|null String data:image/...;base64,... ou null se arquivo não existir
     */
    public static function imagemPublicaParaBase64(string $pathRelativo): ?string
    {
        $pathRelativo = ltrim($pathRelativo, '/');
        $fullPath = public_path($pathRelativo);

        if (!is_file($fullPath) || !is_readable($fullPath)) {
            return null;
        }

        $mtime = @filemtime($fullPath);
        $cacheKey = self::PREFIX_PUBLIC . md5($pathRelativo) . ':' . ($mtime ?: 0);
        if (array_key_exists($cacheKey, self::$memoPublic)) {
            return self::$memoPublic[$cacheKey];
        }

        $result = Cache::remember($cacheKey, now()->addDays(self::TTL_DAYS), function () use ($fullPath) {
            $data = @file_get_contents($fullPath);
            if ($data === false) {
                return null;
            }
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $mime = ($ext === 'pdf') ? 'application' : 'image';
            return "data:{$mime}/{$ext};base64," . base64_encode($data);
        });

        return self::$memoPublic[$cacheKey] = $result;
    }

    /**
     * Retorna array da assinatura com url_thumb.
     * HTML (impressão no browser): URL remota leve. PDF DomPDF: base64 embutido (cache).
     *
     * @return array|null ['nome' => string, 'tipo' => string, 'url_thumb' => string|null]
     */
    public static function assinaturaParaArray(CarteiraAssinatura $a, bool $embedBase64 = true): ?array
    {
        $a->loadMissing('Anexos');
        $anexo = $a->Anexos->first();
        if (!$anexo) {
            return ['nome' => $a->nome, 'tipo' => $a->tipo, 'url_thumb' => null];
        }

        if (!$embedBase64) {
            return ['nome' => $a->nome, 'tipo' => $a->tipo, 'url_thumb' => $anexo->urlThumb];
        }

        $updatedAt = $anexo->updated_at ? $anexo->updated_at->format('U') : '0';
        $cacheKey = self::PREFIX_ASSINATURA . $a->id . ':' . $anexo->id . ':' . $updatedAt;
        if (array_key_exists($cacheKey, self::$memoAssinatura)) {
            $urlThumb = self::$memoAssinatura[$cacheKey];
        } else {
            $urlThumb = Cache::remember($cacheKey, now()->addDays(self::TTL_DAYS), function () use ($anexo) {
                return Sistema::convertBase3($anexo->urlThumb, true);
            });
            self::$memoAssinatura[$cacheKey] = $urlThumb;
        }

        return ['nome' => $a->nome, 'tipo' => $a->tipo, 'url_thumb' => $urlThumb];
    }

    /**
     * Limpa cache de uma assinatura (útil após update/delete de anexo).
     * Como a chave inclui updated_at, na prática só é necessário se quiser forçar renovação.
     */
    public static function limparAssinatura(int $carteiraAssinaturaId): void
    {
        // Laravel cache não suporta forget por prefix; apenas documentar que TTL cuida da renovação
        // Se usar Redis/DynamoDB com tags, poderia: Cache::tags(['carteira_assinatura', $id])->flush();
    }

    /**
     * Foto 3x4 em data URL (JPEG) para carteira/bloqueio.
     * Aceita disco da admissão (fotocurriculo) ou da pré-admissão (documentospreadmissao).
     *
     * @param  string  $file  Nome do arquivo no disco (Arquivo->file)
     * @param  string|null  $disco  Disco preferido do registro; tenta fallbacks se ausente
     */
    public static function fotoCurriculo3x4ParaDataUrl(string $file, ?string $disco = null): ?string
    {
        $discos = array_values(array_unique(array_filter([
            $disco,
            Arquivo::DISCO_FOTOCURRICULO,
            Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO,
        ])));

        foreach ($discos as $discoNome) {
            $dataUrl = self::foto3x4DataUrlDoDisco($file, $discoNome);
            if ($dataUrl !== null) {
                return $dataUrl;
            }
        }

        return null;
    }

    /**
     * Arquivo::$appends regenera `url` no toArray() via accessor — sobrescreve data URL.
     * Por isso a normalização precisa ocorrer no array já serializado.
     *
     * @param  array<string, mixed>  $treinamento
     */
    public static function normalizarFotoTresNoArrayCarteira(array &$treinamento): void
    {
        $fotos = $treinamento['feedback_curriculo']['curriculo']['foto_tres'] ?? null;
        if (!is_array($fotos) || $fotos === []) {
            return;
        }

        $fallback = asset('sem_foto.png');
        foreach ($fotos as $i => $foto) {
            if (!is_array($foto)) {
                continue;
            }
            $file = isset($foto['file']) ? (string) $foto['file'] : '';
            if ($file === '') {
                continue;
            }
            $disco = isset($foto['disco']) ? (string) $foto['disco'] : null;
            $dataUrl = self::fotoCurriculo3x4ParaDataUrl($file, $disco) ?: $fallback;
            $treinamento['feedback_curriculo']['curriculo']['foto_tres'][$i]['url'] = $dataUrl;
            $treinamento['feedback_curriculo']['curriculo']['foto_tres'][$i]['url_base64'] = $dataUrl;
            $treinamento['feedback_curriculo']['curriculo']['foto_tres'][$i]['url_thumb'] = $dataUrl;
        }
    }

    private static function foto3x4DataUrlDoDisco(string $file, string $discoNome): ?string
    {
        try {
            $disk = Storage::disk($discoNome);
        } catch (\Throwable) {
            return null;
        }

        $mtime = 0;
        try {
            if (!$disk->exists($file)) {
                return null;
            }
            $mtime = (int) $disk->lastModified($file);
        } catch (\Throwable) {
            // S3/private: exists pode falhar; tenta ler direto abaixo
        }

        $cacheKey = self::PREFIX_FOTO_PRINT . md5($discoNome . ':' . $file) . ':' . $mtime;
        if (array_key_exists($cacheKey, self::$memoFoto)) {
            return self::$memoFoto[$cacheKey];
        }

        // Não usar Cache::remember com null — null cacheado por dias quebrava a foto
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return self::$memoFoto[$cacheKey] = $cached;
        }

        try {
            if ($discoNome === Arquivo::DISCO_FOTOCURRICULO) {
                $data = Arquivo::getFotocurriculoAnexoContentAndMime($file);
            } else {
                $content = $disk->get($file);
                if ($content === false || $content === null || $content === '') {
                    return null;
                }
                $mime = 'image/jpeg';
                try {
                    $mime = $disk->mimeType($file) ?: $mime;
                } catch (\Throwable) {
                    // ignore
                }
                $data = ['content' => $content, 'mime' => $mime];
            }
        } catch (\Throwable) {
            return null;
        }

        if ($data === null) {
            return null;
        }

        try {
            $img = Image::make($data['content']);
            $img->orientate();
            $img->fit(420, 560, function ($constraint) {
                $constraint->upsize();
            });
            $result = 'data:image/jpeg;base64,' . base64_encode((string) $img->encode('jpg', 82));
        } catch (\Throwable) {
            $result = 'data:' . $data['mime'] . ';base64,' . base64_encode($data['content']);
        }

        Cache::put($cacheKey, $result, now()->addDays(self::TTL_FOTO_PRINT_DAYS));

        return self::$memoFoto[$cacheKey] = $result;
    }
}
