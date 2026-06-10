<?php

namespace App\Data\IntegracaoSpa;

use App\Models\Arquivo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

final class LogoSpaData
{
    public function __construct(
        public readonly ?string $url,
        public readonly ?string $url_thumb,
        public readonly ?bool $imagem,
        public readonly ?string $layout,
        /** UTC `Y-m-d\TH:i:s\Z` a partir de `arquivos.updated_at` (ou `created_at`). */
        public readonly ?string $logo_date,
    ) {
    }

    /**
     * Reidrata a partir do array serializado no cache da listagem SPA.
     */
    public static function fromSpaCacheArray(?array $data): ?self
    {
        if ($data === null) {
            return null;
        }

        return new self(
            url: isset($data['url']) && is_string($data['url']) ? $data['url'] : null,
            url_thumb: isset($data['url_thumb']) && is_string($data['url_thumb']) ? $data['url_thumb'] : null,
            imagem: array_key_exists('imagem', $data) ? (is_bool($data['imagem']) ? $data['imagem'] : null) : null,
            layout: isset($data['layout']) && is_string($data['layout']) ? $data['layout'] : null,
            logo_date: isset($data['logo_date']) && is_string($data['logo_date']) ? $data['logo_date'] : null,
        );
    }

    public static function fromArquivo(?Arquivo $arquivo, ?string $apelido): ?self
    {
        if ($arquivo === null) {
            return null;
        }

        $apelido = is_string($apelido) ? trim($apelido) : '';
        if ($apelido === '') {
            return null;
        }

        $url = self::logotipoUrl($apelido, '0');

        $urlThumb = null;
        if ($arquivo->imagem && $arquivo->thumb) {
            $urlThumb = self::logotipoUrl($apelido, '1');
        } elseif ($arquivo->imagem) {
            $urlThumb = $url;
        }

        return new self(
            url: $url,
            url_thumb: $urlThumb,
            imagem: isset($arquivo->imagem) ? (bool) $arquivo->imagem : null,
            layout: $arquivo->layout ?? null,
            logo_date: self::logoAtIsoUtc($arquivo),
        );
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'url_thumb' => $this->url_thumb,
            'imagem' => $this->imagem,
            'layout' => $this->layout,
            'logo_date' => $this->logo_date,
        ];
    }

    private static function logoAtIsoUtc(Arquivo $arquivo): ?string
    {
        // `Arquivo` usa cast `datetime:d/m/Y H:i:s`; o MySQL grava `Y-m-d H:i:s`, o que pode
        // anular `updated_at`/`created_at` ao hidratar. Preferir valor bruto da linha.
        $rawUpdated = $arquivo->getRawOriginal('updated_at');
        $rawCreated = $arquivo->getRawOriginal('created_at');

        foreach ([$rawUpdated, $rawCreated] as $raw) {
            if (! is_string($raw) || $raw === '') {
                continue;
            }
            try {
                return Carbon::parse($raw)->utc()->format('Y-m-d\TH:i:s\Z');
            } catch (\Throwable) {
                continue;
            }
        }

        $at = $arquivo->updated_at ?? $arquivo->created_at;

        return $at !== null ? $at->clone()->utc()->format('Y-m-d\TH:i:s\Z') : null;
    }

    private static function logotipoUrl(string $apelido, string $thumb): string
    {
        $public = config('integracao_spa.public_url');
        $usePublic = is_string($public) && trim($public) !== '';

        if ($usePublic) {
            URL::forceRootUrl(rtrim($public, '/'));
        }

        try {
            return route('api.integracao_spa.media.logotipo', [
                'apelido' => $apelido,
                'thumb' => $thumb,
            ], true);
        } finally {
            if ($usePublic) {
                URL::forceRootUrl(null);
            }
        }
    }
}
