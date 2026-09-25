<?php

namespace App\Events\WeeklyReport;

use App\Models\TarefaComentario;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComentarioTarefaEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public const INSERT = 'insert';
    public const UPDATE = 'update';
    public const DELETE = 'delete';

    public $obj;
    public $evento;
    public $idDelete;
    public $afterCommit = true;
    public $tarefaId;
    public $listaId;

    public function __construct($obj, string $evento, $idDelete = null, ?int $tarefaId = null, ?int $listaId = null)
    {
        $this->obj = $obj;
        $this->evento = $evento;
        $this->idDelete = $idDelete;
        $this->tarefaId = $tarefaId ?? (int) data_get($obj, 'tarefa_id');
        $this->listaId = $listaId;
    }

    public function broadcastOn()
    {
        $empresa_id = auth()->user()->empresa_id;

        return new PresenceChannel('weekly-report.tarefas.comentarios.' . $empresa_id);
    }

    public function broadcastAs()
    {
        return $this->evento;
    }

    public function broadcastWith()
    {
        return [
            'comentario' => $this->evento === self::DELETE ? null : $this->obj,
            'comentario_id' => $this->idDelete ?? data_get($this->obj, 'id'),
            'tarefa_id' => $this->tarefaId,
            'lista_id' => $this->listaId,
            'evento' => $this->evento,
        ];
    }
}
