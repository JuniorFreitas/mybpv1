<?php

namespace App\Services\Aniversariante;

use App\Models\AniversarianteMensagemTemplate;
use Illuminate\Support\Facades\Cache;

class AniversarianteMensagemResolver
{
    public const CACHE_TTL_SEGUNDOS = 3600;

    public const MENSAGEM_PADRAO = 'Hoje se completa mais um ano da vida de alguém muito importante e valoroso, você {{nome}}!<br><br>Todos nós lhe desejamos um dia muito feliz, que possa celebrar junto da família e amigos e que cumpra muitos anos preenchidos de amor, saúde e paz.<br>Muitos parabéns!<br>';

    public function conteudoHtml(?int $empresaId): string
    {
        if (! $empresaId) {
            return self::MENSAGEM_PADRAO;
        }

        return Cache::remember($this->cacheKey($empresaId), self::CACHE_TTL_SEGUNDOS, function () use ($empresaId) {
            $template = AniversarianteMensagemTemplate::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->first();

            $html = (string) ($template->conteudo_html ?? '');

            return $this->temConteudoUtil($html) ? $html : self::MENSAGEM_PADRAO;
        });
    }

    public function temConteudoUtil(?string $html): bool
    {
        $texto = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = preg_replace('/\x{00a0}/u', ' ', $texto) ?? $texto;

        return trim($texto) !== '';
    }

    public function forgetCache(int $empresaId): void
    {
        Cache::forget($this->cacheKey($empresaId));
    }

    private function cacheKey(int $empresaId): string
    {
        return 'aniversariante_mensagem:v1:' . $empresaId;
    }
}
