<?php

namespace App\Events\WeeklyReport;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ListaEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    const INSERT='insert';
    const UPDATE='update';
    const DELETE='delete';
    const ORDENAR='ordenar';

    public $obj;
    public $evento;
    public $idDelete;
    public $afterCommit = true; // só dispara se for comitado
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($obj,$evento,$idListaDelete=null)
    {
        $this->obj = $obj;
        $this->evento = $evento;
        $this->idDelete = $idListaDelete;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $empresa_id = auth()->user()->empresa_id;
        return new PresenceChannel('weekly-report.listas.'.$empresa_id);
    }

    public function broadcastAs(){
        return $this->evento;
    }

    public function broadcastWith(){
        switch ($this->evento){
            case self::INSERT:
                return [
                    'lista' => $this->obj->Quadro->Listas()->orderBy('ordem')->get()
                ];
                break;
            case self::UPDATE:
                return [
                    'lista' => $this->obj->Quadro->Listas()->orderBy('ordem')->get()
                ];
                break;
            case self::DELETE:
                return [
                    'lista' => $this->obj->Listas()->orderBy('ordem')->get(),
                    'idDelete' => $this->idDelete,
                ];
                break;
            case self::ORDENAR:
                return [
                    'lista' => $this->obj->Listas()->select(['id','ordem'])->orderBy('ordem')->get(),
                ];
                break;
        }
    }
}
