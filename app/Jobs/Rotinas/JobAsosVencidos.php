<?php

namespace App\Jobs\Rotinas;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasterTag\DataHora;

class JobAsosVencidos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct()
    {

    }

    public function __invoke()
    {
        $this->handle();
    }

    public function handle()
    {
        try {
            $examesVencidos = \DB::table('examesesmts')
                ->where('data_vencimento','<',(new DataHora())->dataInsert())
                ->update([
                    'vencido' => 1,
                ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

    }
}
