<?php

namespace App\Events\WeeklyReport;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemChecklistEvent implements ShouldBroadcastNow {
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
    public function __construct($obj, $evento, $idDelete = null) {
        $this->obj = $obj;
        $this->evento = $evento;
        $this->idDelete = $idDelete;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {
        $empresa_id = auth()->user()->empresa_id;
        return new PresenceChannel('weekly-report.tarefas.checklists.itens.' . $empresa_id);
    }

    public function broadcastAs() {
        return $this->evento;
    }

    public function broadcastWith() {
        switch ($this->evento) {
            case self::INSERT:
                return [
                    'itens' => $this->obj->CheckList->Itens()->orderBy('ordem')->get(),
                    'checklist_id' => $this->obj->checklist_id,
                    'tarefa_id' => $this->obj->CheckList->tarefa_id,
                    'lista_id' => $this->obj->CheckList->Tarefa->lista_id,
                ];
                break;
            case self::UPDATE:
                return [
                    'item' => $this->obj,
                    'item_id' => $this->obj->id,
                    'checklist_id' => $this->obj->checklist_id,
                    'tarefa_id' => $this->obj->CheckList->tarefa_id,
                    'lista_id' => $this->obj->CheckList->Tarefa->lista_id,
                ];
                break;
            case self::DELETE:
                return [
                    'item_id' => $this->obj->id,
                    'checklist_id' => $this->obj->checklist_id,
                    'tarefa_id' => $this->obj->CheckList->tarefa_id,
                    'lista_id' => $this->obj->CheckList->Tarefa->lista_id,
                ];
                break;
        }
    }
}
