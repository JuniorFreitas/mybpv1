<?php

namespace App\Jobs;

use App\Services\Aniversariante\AniversarianteEnvioDiaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class JobAniversariantes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $mail;

    public $tries = 3;

    public function __construct($dados)
    {
        $this->mail = [
            'selecionados' => $dados['selecionados'],
            'empresa_id' => $dados['empresa_id'],
        ];
    }

    public function handle(AniversarianteEnvioDiaService $envioService): void
    {
        $ano = (int) date('Y');
        $empresaId = (int) $this->mail['empresa_id'];
        $ids = array_values(array_filter(array_map('intval', (array) $this->mail['selecionados'])));

        if ($ids === []) {
            return;
        }

        $selecionados = DB::table('curriculos')
            ->select(['id', 'nome', 'email'])
            ->whereIn('id', $ids)
            ->whereNull('deleted_at')
            ->get();

        $resumo = [
            'total' => $selecionados->count(),
            'enviados' => 0,
            'erros' => 0,
            'ignorados' => 0,
        ];

        foreach ($selecionados as $aniversariante) {
            $aniversariante->empresa_id = $empresaId;
            $resultado = $envioService->processarAniversariante($aniversariante, $ano);
            $resumo[$resultado]++;
        }

        Log::info('Envio manual de aniversariantes processado', [
            'empresa_id' => $empresaId,
            'total' => $resumo['total'],
            'enviados' => $resumo['enviados'],
            'erros' => $resumo['erros'],
            'ignorados' => $resumo['ignorados'],
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Falha no job manual de aniversariantes', [
            'empresa_id' => $this->mail['empresa_id'] ?? null,
            'erro' => $exception?->getMessage(),
        ]);
    }
}
