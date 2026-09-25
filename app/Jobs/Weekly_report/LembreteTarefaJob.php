<?php

namespace App\Jobs\Weekly_report;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Mail\Weekly_report\LembreteTarefaMail;
use App\Models\Sistema;
use App\Models\Tarefa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use MasterTag\DataHora;

class LembreteTarefaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct()
    {
    }

    /** Compatível com schedule->call(new LembreteTarefaJob) se necessário. */
    public function __invoke()
    {
        $this->handle();
    }

    public function handle()
    {
        $agora = new DataHora();
        $agora->setSegundo(0);
        $inicio = $agora->dataHoraInsert();
        $agora->setSegundo(59);
        $fim = $agora->dataHoraInsert();

        $tarefas = Tarefa::query()
            ->whereBetween('lembrete', [$inicio, $fim])
            ->where('concluido', false)
            ->whereHas('Membros')
            ->with(['Membros', 'Lista'])
            ->get();

        foreach ($tarefas as $tarefa) {
            foreach ($tarefa->Membros as $usuario) {
                try {
                    Event::dispatch(new NotificacaoEvent([
                        'tarefa' => $tarefa,
                        'user_id' => $usuario->id,
                    ], NotificacaoEvent::LEMBRETE_TAREFA, NotificacaoEvent::TIPO_PADRAO));
                } catch (\Throwable $e) {
                    Log::warning('WeeklyReport: falha na notificação de lembrete', [
                        'tarefa_id' => $tarefa->id,
                        'user_id' => $usuario->id,
                        'erro' => $e->getMessage(),
                    ]);
                }

                if (!Sistema::validaEmail($usuario->login ?? '')) {
                    continue;
                }

                try {
                    Mail::send(new LembreteTarefaMail([
                        'para' => $usuario,
                        'modelTarefa' => $tarefa,
                    ]));
                    Log::info('WeeklyReport: e-mail de lembrete enviado', [
                        'tarefa_id' => $tarefa->id,
                        'user_id' => $usuario->id,
                        'janela' => "{$inicio} .. {$fim}",
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('WeeklyReport: falha ao enviar e-mail de lembrete', [
                        'tarefa_id' => $tarefa->id,
                        'user_id' => $usuario->id,
                        'erro' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
