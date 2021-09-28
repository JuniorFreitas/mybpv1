<?php

namespace App\Events\Chat;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MensagemChatEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    const INSERT='insert';
    const UPDATE='update';
    const DELETE='delete';
    const VISTO='visto';

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

        if($this->evento== self::VISTO){
            $obj= $this->obj[0];
            return new PresenceChannel("chat.{$empresa_id}.mensagens.contato.{$obj->de_id}");
        }

        if($this->obj->para_id != null){
            return new PresenceChannel("chat.{$empresa_id}.mensagens.contato.{$this->obj->para_id}");
        }

    }

    public function broadcastAs(){
        return $this->evento;
    }

    public function broadcastWith(){
        switch ($this->evento){
            case self::INSERT:
                return [
                    'mensagem' => $this->obj
                ];
                break;
            case self::UPDATE:
                return [
                    'tarefa' => $this->obj
                ];
                break;
            case self::DELETE:
                return [
                    'tarefas' => $this->obj->Tarefas()->orderBy('ordem')->get(),
                    'idDelete' => $this->idDelete,
                ];
                break;
            case self::VISTO:
                return [
                    'mensagens' => $this->obj,
                ];
                break;
        }
    }
}
