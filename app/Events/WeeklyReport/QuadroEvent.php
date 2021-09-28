<?php

namespace App\Events\WeeklyReport;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuadroEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    const INSERT='insert';
    const UPDATE='update';
    const DELETE='delete';
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $quadro;
    public $evento;
    public $afterCommit = true; // só dispara se for comitado

    public function __construct($quadro,$evento)
    {
        $this->quadro = $quadro;
        $this->evento = $evento;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $empresa_id = auth()->user()->empresa_id;
        return new PresenceChannel('weekly-report.quadros.'.$empresa_id);
    }

    public function broadcastAs(){
        return $this->evento;
    }

    public function broadcastWith(){
        switch ($this->evento){
            case self::INSERT:
                return [
                    'quadro' => $this->quadro
                ];
                break;
            case self::UPDATE:
                return [
                    'quadro' => $this->quadro
                ];
                break;
            case self::DELETE:
                return [
                    'id' => $this->quadro
                ];
                break;
        }
    }
}
