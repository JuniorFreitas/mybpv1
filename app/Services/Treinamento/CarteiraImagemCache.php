<?php

namespace App\Services\Treinamento;

use App\Models\CarteiraAssinatura;
use App\Models\Sistema;
use Illuminate\Support\Facades\Cache;

/**
 * Cache de imagens da carteira de treinamento em base64 para melhorar performance do PDF.
 * Invalidação: chaves incluem path+filemtime (arquivos públicos) ou id+updated_at (assinaturas).
 */
class CarteiraImagemCache
{
    private const PREFIX_PUBLIC = 'carteira_img_b64:';
    private const PREFIX_ASSINATURA = 'carteira_assinatura_b64:';
    private const TTL_DAYS = 30;

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

        return Cache::remember($cacheKey, now()->addDays(self::TTL_DAYS), function () use ($fullPath) {
            $data = @file_get_contents($fullPath);
            if ($data === false) {
                return null;
            }
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $mime = ($ext === 'pdf') ? 'application' : 'image';
            return "data:{$mime}/{$ext};base64," . base64_encode($data);
        });
    }

    /**
     * Retorna array da assinatura com url_thumb em base64 (cache por assinatura + anexo + updated_at).
     * Ao alterar o anexo da assinatura, a chave muda e o cache é renovado.
     *
     * @return array|null ['nome' => string, 'tipo' => string, 'url_thumb' => string|null]
     */
    public static function assinaturaParaArray(CarteiraAssinatura $a): ?array
    {
        $a->loadMissing('Anexos');
        $anexo = $a->Anexos->first();
        if (!$anexo) {
            return ['nome' => $a->nome, 'tipo' => $a->tipo, 'url_thumb' => null];
        }

        $updatedAt = $anexo->updated_at ? $anexo->updated_at->format('U') : '0';
        $cacheKey = self::PREFIX_ASSINATURA . $a->id . ':' . $anexo->id . ':' . $updatedAt;

        $urlThumb = Cache::remember($cacheKey, now()->addDays(self::TTL_DAYS), function () use ($anexo) {
            return Sistema::convertBase3($anexo->urlThumb, true);
        });

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
}
