<?php

namespace App\Jobs\Rotinas;

use App\Services\Aniversariante\AniversarianteEnvioDiaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class JobAniversariantesDia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct()
    {
    }

    public function __invoke(): void
    {
        $this->handle();
    }

    public function handle(): void
    {
        try {
            app(AniversarianteEnvioDiaService::class)->disparar();
        } catch (Throwable $e) {
            Log::error('Falha geral no job de aniversariantes do dia', [
                'erro' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
