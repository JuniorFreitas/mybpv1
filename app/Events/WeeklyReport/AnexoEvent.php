<?php

namespace App\Events\WeeklyReport;

use App\Support\WeeklyReport\WeeklyReportAnexoUrls;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnexoEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    const INSERT = 'insert';
    const UPDATE = 'update';
    const DELETE = 'delete';

    public $obj;
    public $evento;
    public $idDelete;
    public $afterCommit = true; // só dispara se for comitado
    public $acao = null;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($obj, $evento, $idDelete = null)
    {
        $this->obj = $obj;
        $this->evento = $evento;
        $this->idDelete = $idDelete;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $empresa_id = auth()->user()->empresa_id;

        return new PresenceChannel('weekly-report.tarefas.anexos.'.$empresa_id);
    }

    public function broadcastAs()
    {
        return $this->evento;
    }

    public function broadcastWith()
    {
        $lista = $this->obj->Lista;
        $listaId = (int) ($lista->id ?? 0);
        $quadroId = (int) ($lista->quadro_id ?? 0);
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        if ($lista) {
            $lista->loadMissing('Quadro:id,empresa_id');
            if ($lista->Quadro) {
                $empresaId = (int) $lista->Quadro->empresa_id;
            }
        }

        $anexos = ($empresaId > 0 && $quadroId > 0 && $listaId > 0)
            ? WeeklyReportAnexoUrls::mapCollection(
                $this->obj->Anexos()->get(),
                $empresaId,
                $quadroId,
                $listaId,
                (int) $this->obj->id
            )
            : $this->obj->Anexos()->get();

        return [
            'anexos' => $anexos,
            'tarefa_id' => $this->obj->id,
            'lista_id' => $listaId,
        ];
    }
}
