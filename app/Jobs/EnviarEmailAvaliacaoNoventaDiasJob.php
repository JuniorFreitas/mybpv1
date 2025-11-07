<?php

namespace App\Jobs;

use App\Services\AvaliacaoNoventaService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarEmailAvaliacaoNoventaDiasJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $uniqueFor = 3600; // 1 hora

    protected $usuario;
    protected $vencimentos;
    protected $empresaId;
    protected $arquivoS3;

    /**
     * Create a new job instance.
     *
     * @param User $usuario
     * @param array $vencimentos
     * @param int $empresaId
     * @param array|null $arquivoS3
     */
    public function __construct(User $usuario, array $vencimentos, int $empresaId, ?array $arquivoS3 = null)
    {
        $this->usuario = $usuario;
        $this->vencimentos = $vencimentos;
        $this->empresaId = $empresaId;
        $this->arquivoS3 = $arquivoS3;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(AvaliacaoNoventaService $service)
    {
        try {
            $service->enviarEmailVencimentos(
                $this->usuario,
                $this->vencimentos,
                $this->empresaId,
                $this->arquivoS3
            );

            Log::info('Job de envio de e-mail avaliação 90 dias processado com sucesso', [
                'usuario_id' => $this->usuario->id,
                'usuario_email' => $this->usuario->login,
                'empresa_id' => $this->empresaId,
                'total_vencimentos' => count($this->vencimentos)
            ]);

            // Remove o job da fila após sucesso
            $this->delete();

        } catch (\Throwable $e) {
            Log::error('Erro no Job de envio de e-mail avaliação 90 dias', [
                'usuario_id' => $this->usuario->id,
                'usuario_email' => $this->usuario->login,
                'empresa_id' => $this->empresaId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-lança exceção para que o Laravel tente novamente (retries)
            throw $e;
        }
    }

    /**
     * Unique ID para garantir que apenas 1 servidor processe este job por vez
     *
     * @return string
     */
    public function uniqueId()
    {
        return 'avaliacao_90_dias_email_' . $this->usuario->id . '_' . $this->empresaId . '_' . md5(serialize($this->vencimentos));
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Job de envio de e-mail avaliação 90 dias falhou após todas as tentativas', [
            'usuario_id' => $this->usuario->id,
            'usuario_email' => $this->usuario->login,
            'empresa_id' => $this->empresaId,
            'total_attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Remove o job da fila após falha total
        $this->delete();
    }
}
