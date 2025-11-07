<?php

namespace App\Jobs;

use App\Models\AvaliacaoNoventaVencimento;
use App\Services\AvaliacaoNoventaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GerarTokenAvaliacaoNoventaDiasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $feedbackId;
    public int $empresaId;

    public $tries = 1;
    public $timeout = 20; // segs

    public function __construct(int $feedbackId, int $empresaId)
    {
        $this->feedbackId = $feedbackId;
        $this->empresaId = $empresaId;
    }

    public function handle(AvaliacaoNoventaService $service): void
    {
        $lockKey = "avaliacao90dias:token:{$this->feedbackId}";
        $lock = Cache::lock($lockKey, 10);

        if (!$lock->get()) {
            Log::info('GerarTokenAvaliacaoNoventaDiasJob: lock já em uso, ignorando execução concorrente', [
                'feedback_id' => $this->feedbackId,
            ]);
            return;
        }

        try {
            Log::info('GerarTokenAvaliacaoNoventaDiasJob: iniciado', [
                'feedback_id' => $this->feedbackId,
                'empresa_id' => $this->empresaId,
            ]);
            // Em jobs não há usuário autenticado; desabilita escopos globais que dependem de auth/tenant
            $vencimento = AvaliacaoNoventaVencimento::withoutGlobalScopes()
                ->where('feedback_id', $this->feedbackId)
                ->whereHas('FeedbackCurriculo', function ($q) {
                    $q->withoutGlobalScopes()->where('empresa_id', $this->empresaId);
                })
                ->first();

            if (!$vencimento) {
                Log::warning('GerarTokenAvaliacaoNoventaDiasJob: vencimento não encontrado', [
                    'feedback_id' => $this->feedbackId,
                    'empresa_id' => $this->empresaId,
                ]);
                return;
            }

            // Se já existir token válido e não realizado, não faz nada
            if ($vencimento->token_avaliacao && $vencimento->token_expiracao && optional(\Carbon\Carbon::parse($vencimento->token_expiracao))->isFuture() && !$vencimento->avaliacao_realizada) {
                Log::info('GerarTokenAvaliacaoNoventaDiasJob: token já válido, nada a fazer', [
                    'feedback_id' => $this->feedbackId,
                ]);
                return;
            }

            // Gera token (operação pequena de escrita em 1 linha)
            $resultado = $service->gerarTokenAvaliacao($this->feedbackId, 60);

            if (!$resultado) {
                Log::error('GerarTokenAvaliacaoNoventaDiasJob: falha ao gerar token (service retornou null)', [
                    'feedback_id' => $this->feedbackId,
                ]);
                return;
            }

            // Reconsulta para confirmar persistência
            $reloaded = AvaliacaoNoventaVencimento::withoutGlobalScopes()
                ->where('feedback_id', $this->feedbackId)
                ->first(['token_avaliacao','token_expiracao','avaliacao_realizada']);
            Log::info('GerarTokenAvaliacaoNoventaDiasJob: token gerado e persistido?', [
                'feedback_id' => $this->feedbackId,
                'tem_token' => (bool)($reloaded && $reloaded->token_avaliacao),
                'expiracao' => $reloaded && $reloaded->token_expiracao ? (string)$reloaded->token_expiracao : null,
                'avaliacao_realizada' => $reloaded ? (bool)$reloaded->avaliacao_realizada : null,
            ]);

        } catch (\Throwable $e) {
            Log::error('Erro no GerarTokenAvaliacaoNoventaDiasJob', [
                'feedback_id' => $this->feedbackId,
                'empresa_id' => $this->empresaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } finally {
            try {
                $lock->release();
            } catch (\Throwable $e) {
                // noop
            }
        }
    }
}
