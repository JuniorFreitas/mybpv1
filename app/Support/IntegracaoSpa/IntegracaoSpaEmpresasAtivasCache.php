<?php

namespace App\Support\IntegracaoSpa;

use App\Data\IntegracaoSpa\EmpresaSpaData;
use Illuminate\Support\Facades\Cache;

final class IntegracaoSpaEmpresasAtivasCache
{
    /** Versão da chave ao mudar o payload em cache (ex.: `logo_date`). */
    public const CACHE_KEY = 'integracao_spa:v2:empresas_ativas_list_v2';

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function ttlSeconds(): int
    {
        $seconds = (int) config('integracao_spa.empresas_ativas_cache_ttl_seconds', 604800);

        return max(60, $seconds);
    }

    /**
     * @param  callable(): list<EmpresaSpaData>  $resolver
     * @return list<EmpresaSpaData>
     */
    public static function remember(callable $resolver): array
    {
        $payload = Cache::remember(self::CACHE_KEY, now()->addSeconds(self::ttlSeconds()), function () use ($resolver) {
            $dtos = $resolver();

            return array_map(static fn (EmpresaSpaData $dto) => $dto->toArray(), $dtos);
        });

        if (! is_array($payload)) {
            return [];
        }

        return array_map(static fn (array $row) => EmpresaSpaData::fromSpaCacheArray($row), $payload);
    }
}
