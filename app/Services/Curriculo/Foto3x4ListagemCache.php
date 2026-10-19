<?php

namespace App\Services\Curriculo;

use App\Models\Arquivo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

/**
 * Thumb 3x4 para listagens (processo/treinamentos).
 *
 * Na listagem: só lê Redis (rápido). Em miss devolve URL leve e aquece o
 * base64 depois da resposta HTTP — não bloqueia o atualizar com AWS/resize.
 */
class Foto3x4ListagemCache
{
    private const PREFIX = 'foto3x4_list_b64:';

    /** Memo por request. */
    private static array $memo = [];

    /** Paths a aquecer após a resposta (disco+path). */
    private static array $pendentesAquecimento = [];

    /**
     * Uso na listagem: cache hit → data URL; miss → URL do thumb (sem AWS/resize aqui).
     */
    public static function paraListagem(?Arquivo $foto): ?string
    {
        if (!$foto || !$foto->file) {
            return null;
        }

        $disco = (string) ($foto->disco ?: Arquivo::DISCO_FOTOCURRICULO);
        $path = ($foto->imagem && $foto->thumb) ? (string) $foto->thumb : (string) $foto->file;
        if ($path === '') {
            return null;
        }

        $cacheKey = self::chave($disco, $path);
        if (array_key_exists($cacheKey, self::$memo)) {
            return self::$memo[$cacheKey];
        }

        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return self::$memo[$cacheKey] = $cached;
        }

        // Miss: não gera no request da listagem
        self::$pendentesAquecimento[$cacheKey] = ['disco' => $disco, 'path' => $path];

        $url = (string) $foto->urlThumb;

        return self::$memo[$cacheKey] = ($url !== '' ? $url : null);
    }

    /**
     * Agenda geração do base64 após enviar a resposta ao cliente.
     */
    public static function agendarAquecimentoAposResposta(): void
    {
        $pendentes = array_values(self::$pendentesAquecimento);
        self::$pendentesAquecimento = [];

        if ($pendentes === []) {
            return;
        }

        dispatch(function () use ($pendentes) {
            foreach ($pendentes as $item) {
                try {
                    self::gerarECachear((string) $item['disco'], (string) $item['path']);
                } catch (\Throwable) {
                    // aquecimento best-effort — não quebra a listagem
                }
            }
        })->afterResponse();
    }

    /**
     * Gera thumb minúsculo e grava no Redis (uso interno / aquecimento).
     */
    public static function gerarECachear(string $disco, string $path): ?string
    {
        $cacheKey = self::chave($disco, $path);
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return self::$memo[$cacheKey] = $cached;
        }

        $binario = self::lerBinario($disco, $path);
        if ($binario === null) {
            return null;
        }

        try {
            $img = Image::make($binario);
            $img->orientate();
            $img->fit(72, 96, function ($constraint) {
                $constraint->upsize();
            });
            $dataUrl = 'data:image/jpeg;base64,' . base64_encode((string) $img->encode('jpg', 68));
        } catch (\Throwable) {
            $dataUrl = 'data:image/jpeg;base64,' . base64_encode($binario);
        }

        $ttl = (int) config('cache.ttl.foto3x4_listagem', 10800);
        Cache::put($cacheKey, $dataUrl, now()->addSeconds(max(60, $ttl)));

        return self::$memo[$cacheKey] = $dataUrl;
    }

    /**
     * Invalida thumb de listagem (file e/ou thumb no disco).
     */
    public static function forget(string $disco, string $file, ?string $thumb = null): void
    {
        Cache::forget(self::chave($disco, $file));
        unset(self::$memo[self::chave($disco, $file)]);
        unset(self::$pendentesAquecimento[self::chave($disco, $file)]);

        if ($thumb !== null && $thumb !== '') {
            Cache::forget(self::chave($disco, $thumb));
            unset(self::$memo[self::chave($disco, $thumb)]);
            unset(self::$pendentesAquecimento[self::chave($disco, $thumb)]);
        }
    }

    private static function chave(string $disco, string $path): string
    {
        return self::PREFIX . md5($disco . ':' . $path);
    }

    private static function lerBinario(string $disco, string $path): ?string
    {
        try {
            if ($disco === Arquivo::DISCO_FOTOCURRICULO) {
                $data = Arquivo::getFotocurriculoAnexoContentAndMime($path);
                if ($data === null || empty($data['content'])) {
                    return null;
                }

                return $data['content'];
            }

            $disk = Storage::disk($disco);
            if (!$disk->exists($path)) {
                return null;
            }
            $content = $disk->get($path);
            if ($content === false || $content === null || $content === '') {
                return null;
            }

            return $content;
        } catch (\Throwable) {
            return null;
        }
    }
}
