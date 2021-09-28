<?php

namespace App\Events\WeeklyReport;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckListTarefaEvent implements ShouldBroadcastNow {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    const INSERT = 'insert';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const ORDENAR_ITENS = 'ordenar_itens';

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
        return new PresenceChannel('weekly-report.tarefas.checklists.' . $empresa_id);
    }

    public function broadcastAs() {
        return $this->evento;
    }

    public function broadcastWith() {
        switch ($this->evento) {
            case self::INSERT:
                return [
                    'checklists' => $this->obj->Tarefa->Checklists()->with('Itens')->orderBy('ordem')->get(),
                    'tarefa_id' => $this->obj->Tarefa->id,
                    'lista_id' => $this->obj->Tarefa->lista_id,
                ];
                break;
            case self::UPDATE:
                return [
                    'checklists' => $this->obj->Tarefa->Checklists()->with('Itens')->orderBy('ordem')->get(),
                    'tarefa_id' => $this->obj->Tarefa->id,
                    'lista_id' => $this->obj->Tarefa->lista_id,
                ];
                break;
            case self::DELETE:
                return [
                    'checklist_id' => $this->obj->id,
                    'tarefa_id' => $this->obj->Tarefa->id,
                    'lista_id' => $this->obj->Tarefa->lista_id,
                ];
                break;
            case self::ORDENAR_ITENS:
                return [
                    'checklist' => $this->obj->load('Itens'),
                    'tarefa_id' => $this->obj->Tarefa->id,
                    'lista_id' => $this->obj->Tarefa->lista_id,
                ];
                break;
        }
    }
}
