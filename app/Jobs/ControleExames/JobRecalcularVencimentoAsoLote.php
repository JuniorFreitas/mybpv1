<?php

namespace App\Jobs\ControleExames;

use App\Services\ControleExames\AsoPeriodicidadeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MasterTag\DataHora;

class JobRecalcularVencimentoAsoLote implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(
        public int $empresaId,
        public int $exameTipoId,
        public string $unidade,
        public int $valor,
        public int $afterId = 0,
        public ?string $dataAplicacao = null,
        public int $limit = JobRecalcularVencimentoAso::LOTE_LIMIT
    ) {
    }

    public function handle(): void
    {
        if (app(AsoPeriodicidadeService::class)->ehSemVencimento($this->exameTipoId)) {
            // Demissional: zera vencimento residual (não possui validade).
            DB::table('examesesmts as e')
                ->join('exame_funcionarios as ef', 'ef.id', '=', 'e.exame_funcionario_id')
                ->where('e.empresa_id', $this->empresaId)
                ->where('e.atual', 1)
                ->where('ef.exame_tipo_id', $this->exameTipoId)
                ->whereNotNull('e.data_vencimento')
                ->update([
                    'e.data_vencimento' => null,
                    'e.vencido' => 0,
                    'e.updated_at' => now(),
                ]);
            $this->marcarTipoConcluido();

            return;
        }

        $inicio = microtime(true);
        $ids = $this->buscarIdsLote();

        if ($ids === []) {
            $this->marcarTipoConcluido();

            return;
        }

        $afetados = $this->atualizarLote($ids);
        $maxId = max($ids);

        Log::info('Lote de recálculo ASO processado.', [
            'empresa_id' => $this->empresaId,
            'exame_tipo_id' => $this->exameTipoId,
            'after_id' => $this->afterId,
            'qtd' => count($ids),
            'rows_affected' => $afetados,
            'ms' => (int) ((microtime(true) - $inicio) * 1000),
        ]);

        if (count($ids) >= $this->limit) {
            self::dispatch(
                $this->empresaId,
                $this->exameTipoId,
                $this->unidade,
                $this->valor,
                $maxId,
                $this->dataAplicacao,
                $this->limit
            );

            return;
        }

        $this->marcarTipoConcluido();
    }

    /**
     * @return list<int>
     */
    private function buscarIdsLote(): array
    {
        $query = DB::table('examesesmts as e')
            ->join('exame_funcionarios as ef', 'ef.id', '=', 'e.exame_funcionario_id')
            ->where('e.empresa_id', $this->empresaId)
            ->where('e.atual', 1)
            ->where('ef.exame_tipo_id', $this->exameTipoId)
            ->where('e.id', '>', $this->afterId)
            ->orderBy('e.id')
            ->limit($this->limit)
            ->select('e.id');

        if ($this->dataAplicacao) {
            $query->where('e.data_realizacao', '>=', $this->dataAplicacao);
        }

        return $query->pluck('id')->map(static fn ($id) => (int) $id)->all();
    }

    /**
     * @param  list<int>  $ids
     */
    private function atualizarLote(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }

        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return $this->atualizarLoteSqlite($ids);
        }

        $expressao = $this->unidade === AsoPeriodicidadeService::UNIDADE_DIAS
            ? 'DATE_ADD(e.data_realizacao, INTERVAL ? DAY)'
            : 'DATE_SUB(DATE_ADD(e.data_realizacao, INTERVAL ? YEAR), INTERVAL 1 DAY)';

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
            UPDATE examesesmts e
            SET
                e.data_vencimento = {$expressao},
                e.vencido = CASE WHEN ({$expressao}) < CURDATE() THEN 1 ELSE 0 END,
                e.updated_at = NOW()
            WHERE e.id IN ({$placeholders})
              AND e.empresa_id = ?
              AND e.atual = 1
        ";

        $bindings = array_merge(
            [$this->valor, $this->valor],
            $ids,
            [$this->empresaId]
        );

        return DB::update($sql, $bindings);
    }

    /**
     * @param  list<int>  $ids
     */
    private function atualizarLoteSqlite(array $ids): int
    {
        $service = app(AsoPeriodicidadeService::class);
        $config = [
            'unidade' => $this->unidade,
            'valor' => $this->valor,
        ];
        $hoje = (new DataHora())->dataInsert();
        $afetados = 0;

        $rows = DB::table('examesesmts')
            ->whereIn('id', $ids)
            ->where('empresa_id', $this->empresaId)
            ->where('atual', 1)
            ->get(['id', 'data_realizacao']);

        foreach ($rows as $row) {
            if (empty($row->data_realizacao)) {
                continue;
            }

            $vencimentoBr = $service->calcularDataVencimento(
                $row->data_realizacao,
                $this->empresaId,
                $this->exameTipoId,
                $config
            );
            $vencimento = (new DataHora($vencimentoBr))->dataInsert();

            DB::table('examesesmts')->where('id', $row->id)->update([
                'data_vencimento' => $vencimento,
                'vencido' => $vencimento < $hoje ? 1 : 0,
                'updated_at' => now(),
            ]);
            $afetados++;
        }

        return $afetados;
    }

    private function marcarTipoConcluido(): void
    {
        $key = JobRecalcularVencimentoAso::cacheTiposRestantesKey($this->empresaId);
        $restantes = Cache::decrement($key);

        if ($restantes === false || $restantes <= 0) {
            Cache::forget($key);
            Cache::forget(JobRecalcularVencimentoAso::cacheLockKey($this->empresaId));
        }
    }
}
