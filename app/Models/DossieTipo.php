<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DossieTipo extends Model
{
    public const CACHE_VERSION_KEY = 'dossie_tipos_cache_ver';
    public const CACHE_TTL_SEGUNDOS = 1;

    protected $table = 'dossie_tipos';

    protected $fillable = [
        'empresa_id',
        'tipo',
        'chave',
        'label',
        'tipo_modelo',
        'tipo_documento',
        'tem_modelo',
        'permite_assinatura',
        'modelo_arquivo_id',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'empresa_id' => 'int',
        'tipo' => 'string',
        'chave' => 'string',
        'label' => 'string',
        'tipo_modelo' => 'string',
        'tipo_documento' => 'string',
        'tem_modelo' => 'boolean',
        'permite_assinatura' => 'boolean',
        'modelo_arquivo_id' => 'int',
        'ordem' => 'int',
        'ativo' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'empresa_id');
    }

    public function modeloArquivo(): BelongsTo
    {
        return $this->belongsTo(Arquivo::class, 'modelo_arquivo_id');
    }

    public static function limparCache(): void
    {
        Cache::forever(self::CACHE_VERSION_KEY, (string) microtime(true));
    }

    protected static function cacheVersion(): string
    {
        return (string) Cache::get(self::CACHE_VERSION_KEY, '1');
    }

    protected static function cacheKeyAtivos(?int $empresaId): string
    {
        return 'dossie_tipos_ativos_v1_' . self::cacheVersion() . '_' . ($empresaId ?? 'global');
    }

    protected static function cacheKeyTiposConhecidos(): string
    {
        return 'dossie_tipos_conhecidos_v1_' . self::cacheVersion();
    }

    /**
     * Resolve catálogo efetivo: base global + override por empresa.
     * Override com ativo=false remove o tipo da lista daquela empresa.
     */
    public static function ativosOrdenados(?int $empresaId = null): Collection
    {
        return Cache::remember(self::cacheKeyAtivos($empresaId), self::CACHE_TTL_SEGUNDOS, function () use ($empresaId) {
            $rows = self::query()
                ->where(function ($query) use ($empresaId) {
                    $query->whereNull('empresa_id');
                    if ($empresaId) {
                        $query->orWhere('empresa_id', $empresaId);
                    }
                })
                ->orderBy('ordem')
                ->orderBy('id')
                ->get();

            $merged = [];
            foreach ($rows->whereNull('empresa_id') as $row) {
                $merged[$row->tipo] = $row;
            }

            if ($empresaId) {
                foreach ($rows->where('empresa_id', $empresaId) as $row) {
                    $merged[$row->tipo] = $row;
                }
            }

            return collect($merged)
                ->filter(fn (self $item) => (bool) $item->ativo)
                ->sortBy([
                    ['ordem', 'asc'],
                    ['id', 'asc'],
                ])
                ->values();
        });
    }

    /**
     * Tipos conhecidos (qualquer empresa/global) para magic methods de relacionamento.
     *
     * @return array<int, string>
     */
    public static function tiposConhecidos(): array
    {
        return Cache::remember(self::cacheKeyTiposConhecidos(), self::CACHE_TTL_SEGUNDOS, function () {
            return self::query()->distinct()->orderBy('tipo')->pluck('tipo')->all();
        });
    }

    /**
     * @return array<int, string>
     */
    public static function tiposAtivos(?int $empresaId = null): array
    {
        return self::ativosOrdenados($empresaId)->pluck('tipo')->all();
    }

    /**
     * @return array<string, string>
     */
    public static function mapTipoModeloParaDocumento(?int $empresaId = null): array
    {
        $map = [];
        foreach (self::ativosOrdenados($empresaId) as $item) {
            if ($item->permite_assinatura && $item->tipo_modelo && $item->tipo_documento) {
                $map[$item->tipo_modelo] = $item->tipo_documento;
            }
        }

        return $map;
    }

    /**
     * @return array<int, string>
     */
    public static function tiposModeloAssinatura(?int $empresaId = null): array
    {
        return self::ativosOrdenados($empresaId)
            ->filter(fn (self $item) => $item->permite_assinatura && $item->tipo_modelo)
            ->pluck('tipo_modelo')
            ->values()
            ->all();
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::limparCache());
        static::deleted(fn () => self::limparCache());
    }
}
