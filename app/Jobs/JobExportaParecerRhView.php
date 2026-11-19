<?php

namespace App\Jobs;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Exports\Entrevistas\entrevistaRhExport;
use App\Exports\Entrevistas\parecerRhExport;
use App\Models\Exportacao;
use App\Models\FeedbackCurriculo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

/**
 * Exportação assíncrona (padrão CIH) das planilhas FromView de parecer/entrevista RH.
 */
class JobExportaParecerRhView implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 0;

    /**
     * @param  array<int, int|string>  $feedbackIds
     */
    public function __construct(
        public int|string $usuarioId,
        public string $local,
        public string $nomeArquivo,
        public array $feedbackIds,
        public bool $usarEntrevistaRhExport = false,
    ) {
        $this->delay = now()->addSeconds(rand(2, 8));
    }

    public function handle(): void
    {
        $resultado = FeedbackCurriculo::query()
            ->whereIn('id', $this->feedbackIds)
            ->with([
                'Curriculo',
                'parecerRh',
                'parecerRh.individualRh',
                'parecerRh.entrevistaRh',
                'parecerTecnica',
                'parecerRota',
                'parecerTeste',
                'VagaSelecionada',
            ])
            ->get();

        $export = $this->usarEntrevistaRhExport
            ? new entrevistaRhExport($resultado)
            : new parecerRhExport($resultado);

        Exportacao::create([
            'user_id' => (int) $this->usuarioId,
            'arquivo' => $this->nomeArquivo,
            'local' => $this->local,
            'removido' => false,
        ]);

        \Excel::store($export, $this->nomeArquivo, 'disco-exportacao');

        Event::dispatch(new NotificacaoEvent([
            'user_id' => (int) $this->usuarioId,
            'local' => $this->local,
        ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));
    }
}
