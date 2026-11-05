<?php

namespace App\Jobs\ControleExames;

use App\Domain\SaudeOcupacional\Regras\MotorPeriodicidadeAso;
use App\Domain\SaudeOcupacional\Regras\PerfisColaboradoresRepositorio;
use App\Domain\SaudeOcupacional\Regras\Periodicidade;
use App\Domain\SaudeOcupacional\Regras\RegraPeriodicidade;
use App\Domain\SaudeOcupacional\Regras\RegrasAtivasRepositorio;
use App\Services\ControleExames\AsoPeriodicidadeService;
use Carbon\CarbonImmutable;
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

        // Regras da empresa (idade, cargo…) dependem do colaborador: cálculo por linha.
        $regras = app(RegrasAtivasRepositorio::class)->doTipo($this->empresaId, $this->exameTipoId);
        if ($regras !== []) {
            return $this->atualizarLoteComRegras($ids, $regras);
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

    /**
     * Calcula o vencimento de cada ASO com as regras e grava o lote em um único UPDATE (CASE por id).
     *
     * @param  list<int>  $ids
     * @param  list<RegraPeriodicidade>  $regras
     */
    private function atualizarLoteComRegras(array $ids, array $regras): int
    {
        $rows = DB::table('examesesmts')
            ->whereIn('id', $ids)
            ->where('empresa_id', $this->empresaId)
            ->where('atual', 1)
            ->whereNotNull('data_realizacao')
            ->get(['id', 'data_realizacao', 'feedback_id']);

        if ($rows->isEmpty()) {
            return 0;
        }

        $perfis = app(PerfisColaboradoresRepositorio::class)->carregarVarios(
            $rows->pluck('feedback_id')->filter()->map(fn ($id) => (int) $id)->all()
        );
        $motor = app(MotorPeriodicidadeAso::class);
        $padrao = new Periodicidade($this->unidade, max(1, $this->valor));
        $hoje = CarbonImmutable::today();

        $casosVencimento = [];
        $casosVencido = [];
        $bindVencimento = [];
        $bindVencido = [];
        $idsAtualizar = [];

        foreach ($rows as $row) {
            $perfil = $row->feedback_id ? ($perfis[(int) $row->feedback_id] ?? null) : null;
            $vencimento = $motor->calcular(CarbonImmutable::parse($row->data_realizacao), $padrao, $perfil, $regras)->vencimento;

            $casosVencimento[] = 'WHEN ? THEN ?';
            array_push($bindVencimento, (int) $row->id, $vencimento->toDateString());
            $casosVencido[] = 'WHEN ? THEN ?';
            array_push($bindVencido, (int) $row->id, $vencimento->lessThan($hoje) ? 1 : 0);
            $idsAtualizar[] = (int) $row->id;
        }

        $placeholders = implode(',', array_fill(0, count($idsAtualizar), '?'));
        $sql = 'UPDATE examesesmts SET '
            . 'data_vencimento = CASE id ' . implode(' ', $casosVencimento) . ' END, '
            . 'vencido = CASE id ' . implode(' ', $casosVencido) . ' END, '
            . 'updated_at = ? '
            . "WHERE id IN ({$placeholders}) AND empresa_id = ? AND atual = 1";

        return DB::update($sql, array_merge(
            $bindVencimento,
            $bindVencido,
            [now()->toDateTimeString()],
            $idsAtualizar,
            [$this->empresaId]
        ));
    }

    private function marcarTipoConcluido(): void
    {
        // UPDATE em SQL não dispara eventos do model: atualiza o Painel aqui.
        \App\Domain\SaudeOcupacional\Painel\PainelCache::invalidar($this->empresaId);

        $key = JobRecalcularVencimentoAso::cacheTiposRestantesKey($this->empresaId);
        $restantes = Cache::decrement($key);

        if ($restantes === false || $restantes <= 0) {
            Cache::forget($key);
            Cache::forget(JobRecalcularVencimentoAso::cacheLockKey($this->empresaId));
        }
    }
}
