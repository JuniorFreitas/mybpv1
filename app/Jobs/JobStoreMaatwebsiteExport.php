<?php

namespace App\Jobs;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\Exportacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

/**
 * Padrão CIH para exports Maatwebsite (FromView/FromCollection/etc.):
 * Exportacao + Excel::store(disco-exportacao) + notificação.
 */
class JobStoreMaatwebsiteExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 0;

    /**
     * @param  class-string  $exportClass
     * @param  array<int, mixed>  $constructorArgs  Argumentos do construtor do Export (serializáveis)
     */
    public function __construct(
        public int|string $usuarioId,
        public string $local,
        public string $nomeArquivo,
        public string $exportClass,
        public array $constructorArgs = [],
    ) {
        $this->delay = now()->addSeconds(rand(2, 8));
    }

    public function handle(): void
    {
        if (!class_exists($this->exportClass)) {
            throw new \InvalidArgumentException("Export class inexistente: {$this->exportClass}");
        }

        $export = new ($this->exportClass)(...$this->constructorArgs);

        Exportacao::create([
            'user_id' => (int) $this->usuarioId,
            'arquivo' => $this->nomeArquivo,
            'local' => $this->local,
            'removido' => false,
        ]);

        \Excel::store($export, $this->nomeArquivo, 'disco-exportacao');

        Event::dispatch(new NotificacaoEvent([
            'user_id' => (int) $this->usuarioId,
            'local' => $this->local,
        ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));
    }
}
