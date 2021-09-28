<?php

namespace App\Events\WeeklyReport;

use App\Models\LogWeekly;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LogWeeklyEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $obj;
    public $idDelete;
    public $afterCommit = true; // só dispara se for comitado
    public $acao = null;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(LogWeekly $obj)
    {
        $this->obj = $obj;
        $this->obj->load('Usuario:id,nome');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $empresa_id = auth()->user()->empresa_id;
        return new PresenceChannel('weekly-report.log.'.$empresa_id);
    }

    public function broadcastAs(){
        return 'log';
    }

    public function broadcastWith(){
        return [
            'log'=>$this->obj
        ];
    }
}
