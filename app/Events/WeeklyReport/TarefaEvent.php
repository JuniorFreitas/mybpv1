<?php

namespace App\Events\WeeklyReport;

use App\Support\WeeklyReport\WeeklyReportEagerLoads;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TarefaEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    const INSERT='insert';
    const UPDATE='update';
    const DELETE='delete';
    const ORDENAR='ordenar';
    const ORDENAR_CHECKLIST='ordenarChecklist';
    const UPDATE_MEMBROS='updateMembro';
    const UPDATE_DATAHORA_ENTREGA='updateDataHoraEntrega';
    const UPDATE_DATAHORA_INICIO='updateDataHoraInicio';
    const ACAO_ADD='add';
    const ACAO_DELETE='remove';

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
    public function __construct($obj,$evento,$idDelete=null)
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
        return new PresenceChannel('weekly-report.tarefas.'.$empresa_id);
    }

    public function broadcastAs(){
        return $this->evento;
    }

    public function broadcastWith(){
        switch ($this->evento){
            case self::INSERT:
                return [
                    'tarefas' => WeeklyReportEagerLoads::applyBoardTarefas(
                        $this->obj->Lista->Tarefas()
                    )->get(),
                ];
            case self::UPDATE:
                return [
                    'tarefa' => $this->obj
                ];
            case self::DELETE:
                return [
                    'tarefas' => WeeklyReportEagerLoads::applyBoardTarefas(
                        $this->obj->Tarefas()
                    )->get(),
                    'idDelete' => $this->idDelete,
                ];
            case self::ORDENAR:
                return [
                    'tarefas' => WeeklyReportEagerLoads::applyBoardTarefas(
                        $this->obj->Tarefas()
                    )->get(),
                    'lista_id' => $this->obj->id // aqui chega o objeto Lista
                ];
            case self::ORDENAR_CHECKLIST:
                return [
                    'checklists' => $this->obj->Checklists()
                        ->with(['Itens' => function ($q) {
                            $q->orderBy('ordem');
                        }])
                        ->orderBy('ordem')
                        ->get(),
                    'tarafa_id' => $this->obj->id,
                    'lista_id' => $this->obj->Lista->id,
                ];
            case self::UPDATE_MEMBROS:
                return [
                    'membros' => $this->obj->Membros()->get(['users.id', 'users.nome']),
                    'tarefa_id' => $this->obj->id,
                    'acao' => $this->acao,
                ];
            case self::UPDATE_DATAHORA_ENTREGA:
                return [
                    'datahora_entrega' => $this->obj->dataHoraEntregaFormatada,
                    'tarefa_id' => $this->obj->id,
                    'concluido' => $this->obj->concluido,
                    'emAtraso' => $this->obj->emAtraso,
                    'acao' => $this->acao,
                ];
            case self::UPDATE_DATAHORA_INICIO:
                return [
                    'datahora_inicio' => $this->obj->dataHoraInicioFormatada,
                    'tarefa_id' => $this->obj->id,
                    'acao' => $this->acao,
                ];
        }
    }
}
