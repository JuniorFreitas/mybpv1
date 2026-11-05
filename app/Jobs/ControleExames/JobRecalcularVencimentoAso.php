<?php

namespace App\Jobs\ControleExames;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JobRecalcularVencimentoAso implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const CACHE_LOCK_TTL = 3600;

    public const LOTE_LIMIT = 300;

    public $tries = 3;

    /**
     * @param  list<array{exame_tipo_id:int,unidade:string,valor:int}>  $tipos
     */
    public function __construct(
        public int $empresaId,
        public array $tipos,
        public ?string $dataAplicacao = null
    ) {
    }

    public static function cacheLockKey(int $empresaId): string
    {
        return 'aso:recalc:' . $empresaId;
    }

    public static function cacheTiposRestantesKey(int $empresaId): string
    {
        return 'aso:recalc:' . $empresaId . ':tipos';
    }

    public function handle(): void
    {
        $lockKey = self::cacheLockKey($this->empresaId);

        if (!Cache::add($lockKey, 1, self::CACHE_LOCK_TTL)) {
            Log::info('Recálculo ASO já em andamento; job orquestrador ignorado.', [
                'empresa_id' => $this->empresaId,
            ]);

            return;
        }

        $tipos = array_values(array_filter($this->tipos, static function ($tipo) {
            return isset($tipo['exame_tipo_id'], $tipo['unidade'], $tipo['valor']);
        }));

        if ($tipos === []) {
            Cache::forget($lockKey);

            return;
        }

        Cache::put(self::cacheTiposRestantesKey($this->empresaId), count($tipos), self::CACHE_LOCK_TTL);

        foreach ($tipos as $tipo) {
            JobRecalcularVencimentoAsoLote::dispatch(
                $this->empresaId,
                (int) $tipo['exame_tipo_id'],
                (string) $tipo['unidade'],
                (int) $tipo['valor'],
                0,
                $this->dataAplicacao,
                self::LOTE_LIMIT
            );
        }

        Log::info('Recálculo ASO orquestrado.', [
            'empresa_id' => $this->empresaId,
            'tipos' => count($tipos),
            'data_aplicacao' => $this->dataAplicacao,
        ]);
    }
}
